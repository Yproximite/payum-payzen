<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Request;

use Payum\Core\Request\GetStatusInterface as Request;
use Yproximite\Payum\Payzen\Api;

class RequestStatusApplier
{
    /** @var array<string, callable(Request):void> */
    protected array $appliers = [];

    public function __construct()
    {
        $this->appliers[Api::STATUS_ABANDONED]                         = fn (Request $request) => $request->markCanceled();
        $this->appliers[Api::STATUS_AUTHORISED]                        = fn (Request $request) => $request->markAuthorized();
        $this->appliers[Api::STATUS_AUTHORISED_TO_VALIDATE]            = fn (Request $request) => $request->markPending();
        $this->appliers[Api::STATUS_CANCELLED]                         = fn (Request $request) => $request->markCanceled();
        $this->appliers[Api::STATUS_CAPTURED]                          = fn (Request $request) => $request->markCaptured();
        $this->appliers[Api::STATUS_CAPTURE_FAILED]                    = fn (Request $request) => $request->markFailed();
        $this->appliers[Api::STATUS_EXPIRED]                           = fn (Request $request) => $request->markExpired();
        $this->appliers[Api::STATUS_INITIAL]                           = fn (Request $request) => $request->markNew();
        $this->appliers[Api::STATUS_NOT_CREATED]                       = fn (Request $request) => $request->markUnknown();
        $this->appliers[Api::STATUS_REFUSED]                           = fn (Request $request) => $request->markCanceled();
        $this->appliers[Api::STATUS_SUSPENDED]                         = fn (Request $request) => $request->markSuspended();
        $this->appliers[Api::STATUS_UNDER_VERIFICATION]                = fn (Request $request) => $request->markPending();
        $this->appliers[Api::STATUS_WAITING_AUTHORISATION]             = fn (Request $request) => $request->markPending();
        $this->appliers[Api::STATUS_WAITING_AUTHORISATION_TO_VALIDATE] = fn (Request $request) => $request->markPending();
    }

    public function apply(?string $status, Request $request): void
    {
        if (null === $status) {
            $request->markNew();

            return;
        }

        if (!\array_key_exists($status, $this->appliers)) {
            $request->markUnknown();

            return;
        }

        $this->appliers[$status]($request);
    }
}
