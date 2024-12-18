<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Tests;

use Payum\Core\Bridge\Guzzle\HttpClientFactory;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\LogicException;
use PHPUnit\Framework\TestCase;
use Yproximite\Payum\Payzen\PayzenGatewayFactory;

class PayzenGatewayFactoryTest extends TestCase
{
    /**
     * @test
     */
    public function shouldSubClassGatewayFactory(): void
    {
        $rc = new \ReflectionClass('Yproximite\Payum\Payzen\PayzenGatewayFactory');

        static::assertTrue($rc->isSubclassOf('Payum\Core\GatewayFactory'));
    }

    /**
     * @test
     */
    public function shouldThrowIfRequiredOptionsAreNotPassed(): void
    {
        $factory = new PayzenGatewayFactory();

        static::expectException(LogicException::class);
        static::expectExceptionMessage('The vads_site_id, certif_test, certif_prod fields are required.');
        $factory->create();
    }

    /**
     * @test
     */
    public function testDefaultOptions(): void
    {
        $factory = new PayzenGatewayFactory();

        $config = $factory->createConfig();

        static::assertSame('payzen', $config['payum.factory_name']);
        static::assertSame('payzen', $config['payum.factory_title']);

        static::assertInstanceOf('Yproximite\Payum\Payzen\Request\RequestStatusApplier', $config['payum.request_status_applier']);

        static::assertInstanceOf('Yproximite\Payum\Payzen\Action\CaptureAction', $config['payum.action.capture']);
        static::assertInstanceOf('Yproximite\Payum\Payzen\Action\NotifyAction', $config['payum.action.notify']);
        static::assertInstanceOf('Yproximite\Payum\Payzen\Action\StatusAction', $config['payum.action.status'](ArrayObject::ensureArrayObject($config)));
        static::assertInstanceOf('Yproximite\Payum\Payzen\Action\ConvertPaymentAction', $config['payum.action.convert_payment']);

        static::assertNull($config['payum.default_options']['vads_site_id']);
        static::assertSame('INTERACTIVE', $config['payum.default_options']['vads_action_mode']);
        static::assertSame('PAYMENT', $config['payum.default_options']['vads_page_action']);
        static::assertSame('SINGLE', $config['payum.default_options']['vads_payment_config']);
        static::assertSame('V2', $config['payum.default_options']['vads_version']);
        static::assertTrue($config['payum.default_options']['sandbox']);
        static::assertNull($config['payum.default_options']['certif_prod']);
        static::assertNull($config['payum.default_options']['certif_test']);
        static::assertEquals('algo-sha1', $config['payum.default_options']['hash_algorithm']);
        static::assertEquals([
            'vads_site_id',
            'vads_action_mode',
            'vads_page_action',
            'certif_test',
            'certif_prod',
            'endpoint'
        ], $config['payum.required_options']);

        static::assertInstanceOf(\Closure::class, $config['payum.api']);
    }
}
