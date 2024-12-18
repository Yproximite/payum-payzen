<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Tests\Action;

use Payum\Core\Model\Token;
use Payum\Core\Request\Capture;
use Payum\Core\Security\GenericTokenFactoryInterface;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;
use Yproximite\Payum\Payzen\Action\CaptureAction;
use Yproximite\Payum\Payzen\Api;

class CaptureActionTest extends GenericActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass = CaptureAction::class;

    /**
     * @test
     */
    public function shouldDoNothingIfPaymentHasResult(): void
    {
        $model = [
            'vads_result' => Api::STATUS_CAPTURED,
        ];

        /** @var MockObject&Api $apiMock */
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects(static::never())
            ->method('doPayment');

        $action = new CaptureAction();
        $action->setApi($apiMock);

        $action->execute(new Capture($model));
    }

    /**
     * @test
     */
    public function shouldGenerateNotifyTokenIfNoOneIsPassed(): void
    {
        $model = new \ArrayObject([]);

        $captureToken = new Token();
        $captureToken->setGatewayName('theGatewayName');
        $captureToken->setTargetUrl('theReturnUrl');
        $captureToken->setAfterUrl('theAfterUrl');
        $captureToken->setDetails($model);

        $notifyToken = new Token();
        $notifyToken->setTargetUrl('theNotifyUrl');

        $tokenFactoryMock = $this->createMock(GenericTokenFactoryInterface::class);
        $tokenFactoryMock
            ->expects(static::once())
            ->method('createNotifyToken')
            ->with('theGatewayName', $model)
            ->will(static::returnValue($notifyToken));

        /** @var MockObject&Api $apiMock */
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects(static::once())
            ->method('doPayment')
            ->with([
                'vads_url_return' => 'theReturnUrl',
                'vads_url_check'  => 'theNotifyUrl',
                'vads_url_cancel' => 'theAfterUrl',
            ])
        ;

        $action = new CaptureAction();
        $action->setApi($apiMock);
        $action->setGenericTokenFactory($tokenFactoryMock);

        $request = new Capture($captureToken);
        $request->setModel($model);

        $action->execute($request);

        static::assertArrayHasKey('vads_url_return', $model);
        static::assertEquals('theReturnUrl', $model['vads_url_return']);

        static::assertArrayHasKey('vads_url_check', $model);
        static::assertEquals('theNotifyUrl', $model['vads_url_check']);
    }

    /**
     * @return MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->createMock(Api::class);
    }
}
