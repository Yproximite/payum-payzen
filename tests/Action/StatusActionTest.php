<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Tests\Action;

use Payum\Core\Request\GetHumanStatus;
use Payum\Core\Tests\GenericActionTest;
use Yproximite\Payum\Payzen\Action\StatusAction;
use Yproximite\Payum\Payzen\Request\RequestStatusApplier;

class StatusActionTest extends GenericActionTest
{
    protected $requestClass = GetHumanStatus::class;
    protected $actionClass  = StatusAction::class;

    protected function setUp(): void
    {
        $this->action = new $this->actionClass(new RequestStatusApplier());
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments(): void
    {
        static::markTestSkipped();
    }

    /**
     * @test
     * @dataProvider provideMarkRequest
     */
    public function shouldMarkRequest(?string $status, string $expectedRequestStatus): void
    {
        $model = new \ArrayObject([
            'vads_trans_status' => $status,
        ]);

        $this->action->execute($request = new GetHumanStatus($model));

        static::assertEquals($expectedRequestStatus, $request->getValue());
    }

    public function provideMarkRequest(): \Generator
    {
        yield [null, 'new'];
        yield ['qsdqsd', 'unknown'];
        yield ['ABANDONED', 'canceled'];
        yield ['AUTHORISED', 'authorized'];
        yield ['AUTHORISED_TO_VALIDATE', 'pending'];
        yield ['CANCELLED', 'canceled'];
        yield ['CAPTURED', 'captured'];
        yield ['CAPTURE_FAILED', 'failed'];
        yield ['EXPIRED', 'expired'];
        yield ['INITIAL', 'new'];
        yield ['NOT_CREATED', 'unknown'];
        yield ['REFUSED', 'canceled'];
        yield ['SUSPENDED', 'suspended'];
        yield ['UNDER_VERIFICATION', 'pending'];
        yield ['WAITING_AUTHORISATION', 'pending'];
        yield ['WAITING_AUTHORISATION_TO_VALIDATE', 'pending'];
    }
}
