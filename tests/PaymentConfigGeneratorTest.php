<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Tests;

use PHPUnit\Framework\TestCase;
use Yproximite\Payum\Payzen\PaymentConfigGenerator;

class PaymentConfigGeneratorTest extends TestCase
{
    /**
     * @dataProvider provideGoodInput
     */
    public function testGoodInput(string $expectedOutput, mixed $input): void
    {
        $paymentConfigGenerator = new PaymentConfigGenerator();

        $output = $paymentConfigGenerator->generate($input);

        static::assertSame($expectedOutput, $output);
    }

    public function provideGoodInput(): \Generator
    {
        yield ['SINGLE', 'SINGLE'];
        yield ['SINGLE', 'single'];
        yield ['MULTI:first=1000;count=3;period=30', ['amount' => 3000, 'count' => 3, 'period' => 30]];

        $today = \DateTimeImmutable::createFromFormat('Y-m-d', '2014-02-01');

        yield [
            'MULTI_EXT:20140201=1000;20140301=1500;20140401=2000',
            [
                ['date' => $today, 'amount' => 1000],
                ['date' => $today->add(new \DateInterval('P1M')), 'amount' => 1500],
                ['date' => $today->add(new \DateInterval('P2M')), 'amount' => 2000],
            ],
        ];
    }

    /**
     * @dataProvider provideBadInput
     */
    public function testBadInput(string $expectedException, mixed $input): void
    {
        $this->expectExceptionMessage($expectedException);

        $paymentConfigGenerator = new PaymentConfigGenerator();

        $paymentConfigGenerator->generate($input);
    }

    public function provideBadInput(): \Generator
    {
        yield ['The given input "ABCDEF" is not a valid payment configuration, valid values are "SINGLE".', 'abcdef'];
        yield ['The given input "123" should be a string or an array.', 123];
        yield ['The given input array is not valid. It should be either an array with keys "amount", "count", and "period", or an array of arrays with keys "date" and "amount".', ['foo' => 'bar']];
    }
}
