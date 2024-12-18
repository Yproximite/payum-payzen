<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Tests\Action;

use Payum\Core\Action\GetCurrencyAction;
use Payum\Core\GatewayInterface;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Payum\Core\Tests\GenericActionTest;
use Payum\Core\Tests\Mocks\Entity\Payment;
use PHPUnit\Framework\MockObject\MockObject;
use Yproximite\Payum\Payzen\Action\ConvertPaymentAction;

class ConvertPaymentActionTest extends GenericActionTest
{
    protected $requestClass = Convert::class;

    protected $actionClass = ConvertPaymentAction::class;

    public function provideSupportedRequests(): \Generator
    {
        yield [new $this->requestClass(new Payment(), 'array')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'array')];
        yield [new $this->requestClass(new Payment(), 'array', $this->createMock('Payum\Core\Security\TokenInterface'))];
    }

    public function provideNotSupportedRequests(): \Generator
    {
        yield ['foo'];
        yield [['foo']];
        yield [new \stdClass()];
        yield [$this->getMockForAbstractClass('Payum\Core\Request\Generic', [[]])];
        yield [new $this->requestClass(new \stdClass(), 'array')];
        yield [new $this->requestClass(new Payment(), 'foobar')];
        yield [new $this->requestClass($this->createMock(PaymentInterface::class), 'foobar')];
    }

    /**
     * @test
     */
    public function shouldCorrectlyConvertOrderToDetailsAndSetItBack(): void
    {
        $payment = new Payment();
        $payment->setNumber('354');
        $payment->setCurrencyCode('EUR');
        $payment->setTotalAmount(123);
        $payment->setClientId('theClientId');
        $payment->setClientEmail('theClientEmail');

        /** @var MockObject&GatewayInterface $gatewayMock */
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects(static::once())
            ->method('execute')
            ->with(static::isInstanceOf('Payum\Core\Request\GetCurrency'))
            ->willReturnCallback(function (GetCurrency $request) {
                $action = new GetCurrencyAction();
                $action->execute($request);
            });

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $action->execute($convert = new Convert($payment, 'array'));

        $details = $convert->getResult();

        static::assertNotEmpty($details);

        static::assertArrayHasKey('vads_trans_id', $details);
        static::assertSame('000354', $details['vads_trans_id']);

        static::assertArrayHasKey('vads_trans_date', $details);
        static::assertMatchesRegularExpression('#^20[0-9]{2}\d{2}\d{2}\d{2}\d{2}\d{2}$#', $details['vads_trans_date']);

        static::assertArrayHasKey('vads_amount', $details);
        static::assertSame(123, $details['vads_amount']);

        static::assertArrayHasKey('vads_cust_id', $details);
        static::assertSame('theClientId', $details['vads_cust_id']);

        static::assertArrayHasKey('vads_cust_email', $details);
        static::assertSame('theClientEmail', $details['vads_cust_email']);

        static::assertArrayHasKey('vads_currency', $details);
        static::assertSame('978', $details['vads_currency']);
    }
}
