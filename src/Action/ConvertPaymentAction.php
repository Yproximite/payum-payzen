<?php

declare(strict_types=1);

namespace Yproximite\Payum\Payzen\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;
use Yproximite\Payum\Payzen\Api;

class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    public function execute($request): void
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();
        $details = ArrayObject::ensureArrayObject($payment->getDetails());

        $details[Api::FIELD_VADS_TRANS_ID]       = \sprintf('%06d', $payment->getNumber());
        $details[Api::FIELD_VADS_TRANS_DATE]     = gmdate('YmdHis');
        $details[Api::FIELD_VADS_AMOUNT]         = $payment->getTotalAmount();
        $details[Api::FIELD_VADS_CUSTOMER_ID]    = $payment->getClientId();
        $details[Api::FIELD_VADS_CUSTOMER_EMAIL] = $payment->getClientEmail();

        $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
        $details[Api::FIELD_VADS_CURRENCY] = $currency->numeric;

        $request->setResult((array) $details);
    }

    public function supports($request): bool
    {
        return
            $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && 'array' === $request->getTo();
    }
}
