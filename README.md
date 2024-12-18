# Payum Payzen

> A Payum gateway to use [Payzen](https://payzen.io/)

[![Latest Stable Version](https://poser.pugx.org/yproximite/payum-payzen/version)](https://packagist.org/packages/yproximite/payum-payzen)
[![Build](https://github.com/Yproximite/payum-payzen/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/Yproximite/payum-payzen/actions/workflows/ci.yml)

## Requirements

- PHP 8.1+
- [Payum](https://github.com/Payum/Payum)
- Optionally [PayumBundle](https://github.com/Payum/PayumBundle) and Symfony 5+

## Installation

```bash
$ composer require yproximite/payum-payzen
```

## Configuration

### With PayumBundle (Symfony)

First register the gateway factory in your services definition:
```yaml
# config/services.yaml
services:
    yproximite.payzen_gateway_factory:
        class: Payum\Core\Bridge\Symfony\Builder\GatewayFactoryBuilder
        arguments: [Yproximite\Payum\SystemPay\PayzenGatewayFactory]
        tags:
            - { name: payum.gateway_factory_builder, factory: payzen }
```

Then configure the gateway:

```yaml
# config/packages/payum.yaml

payum:
  gateways:
    payzen:
      factory: payzen
      vads_site_id: 'change it' # required 
      certif_prod: 'change it' # required 
      certif_test: 'change it' # required 
      sandbox: true
      hash_algorithm: 'algo-sha1' # or 'algo-hmac-sha256'
      endpoint: 'systempay' # 'systempay', 'sogecommerce', 'payzen'
```

### With Payum

```php
<?php
//config.php

use Payum\Core\PayumBuilder;
use Payum\Core\Payum;

/** @var Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()

    ->addGateway('gatewayName', [
        'factory'        => 'payzen',
        'vads_site_id'   => 'change it', // required
        'certif_prod'    => 'change it', // required
        'certif_test'    => 'change it', // required
        'sandbox'        => true,
        'hash_algorithm' => 'algo-sha1' // or 'algo-hmac-sha256'
        'endpoint'       => 'systempay' // 'system-pay', 'sogecommerce', 'payzen'
    ])

    ->getPayum()
;
```

### Why `hash_algorithm` is prefixed by `algo-`?

We wanted to use `sha1` or `hmac-256`, but there is currently a [Payum limitation](https://github.com/Payum/Payum/issues/692) which try to call `sha1` because it's a valid callable.

As a workaround, the only easy solution we thought was to prefix them with `algo-`.
Since `algo-sha1` is not a valid callable, there is no Payum issues and everything works well. 

## Usage

Make sure your `Payment` entity overrides `getNumber()` method like this:
```php
<?php

namespace App\Entity\Payment;

use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\Payment as BasePayment;

/**
 * @ORM\Table
 * @ORM\Entity
 */
class Payment extends BasePayment
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    protected $id;

    /**
     * {@inheritdoc}
     */
    public function getNumber()
    {
        return (string) $this->id;
    }
}
```

By doing this, the library will be able to pick the payment's id and use it for the payment with System Pay (we should send a transaction id between `000000` and `999999`). 

### Payment in several instalments

If you planned to support payments in several instalments, somewhere in your code you will need to call `Payment#setPartialAmount` to keep a trace of the amount per payment:

```php
<?php
class Payment extends BasePayment
{
    // ...

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $partialAmount;

    public function getPartialAmount(): ?int
    {
        return $this->partialAmount;
    }

    public function setPartialAmount(?int $partialAmount): void
    {
        $this->partialAmount = $partialAmount;
    }
}
```

#### Usage

```php
<?php

use App\Entity\Payment;
use Yproximite\Payum\Payzen\Api;
use Yproximite\Payum\Payzen\PaymentConfigGenerator;

// Define the periods
$periods = [
    ['amount' => 1000, 'date' => new \DateTime()],
    ['amount' => 2000, 'date' => (new \DateTime())->add(new \DateInterval('P1M'))],
    ['amount' => 3000, 'date' => (new \DateTime())->add(new \DateInterval('P2M'))],
];

// Compute total amount
$totalAmount = array_sum(array_column($periods, 'amount'));

// Compute `paymentConfig` fields that will be sent to the API
// It will generates something like this: MULTI_EXT:20190102=1000;20190202=2000;20190302=3000
$paymentConfig = (new PaymentConfigGenerator())->generate($periods);

// Then create payments
$storage = $payum->getStorage(Payment::class);
$payments = [];

foreach ($periods as $period) {
    $payment = $storage->create();
    $payment->setTotalAmount($totalAmount);
    $payment->setPartialAmount($period['amount']);

    $details = $payment->getDetails();
    $details[Api::FIELD_VADS_PAYMENT_CONFIG] = $generatedPaymentConfig;
    $payment->setDetails($details);

    $storage->update($payment);
    $payments[] = $payment;
}
```
