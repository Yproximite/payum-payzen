<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;
use Yproximite\Payum\Payzen\Api;
use Yproximite\Payum\Payzen\Request\RequestStatusApplier;

class StatusAction implements ActionInterface
{
    /** @var RequestStatusApplier */
    private $requestStatusApplier;

    public function __construct(RequestStatusApplier $requestStatusApplier)
    {
        $this->requestStatusApplier = $requestStatusApplier;
    }

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->requestStatusApplier->apply($model[Api::FIELD_VADS_TRANS_STATUS], $request);
    }

    public function supports($request): bool
    {
        return $request instanceof GetStatusInterface && $request->getModel() instanceof \ArrayAccess;
    }
}
