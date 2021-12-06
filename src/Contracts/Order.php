<?php

namespace Adscom\LarapackRiskified\Contracts;

use Adscom\LarapackRiskified\DataTransferObjects\RiskifiedData;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Riskified\OrderWebhook\Model\Address as RiskifiedAddress;
use Riskified\OrderWebhook\Model\Order as RiskifiedOrder;

abstract class Order
{
  public function __construct(protected Model $model)
  {
  }

  abstract public function riskifiedLogs(): HasMany;

  public function getUuid(): string
  {
    return (string)$this->model->uuid;
  }

  public function getId(): int
  {
    return $this->model->id;
  }

  abstract public function getAddressCountryIso(): string;

  /**
   * @throws Exception
   */
  public function getAddress(): RiskifiedAddress
  {
    $countryIsoCode = $this->getAddressCountryIso();

    return new RiskifiedAddress([
      'country_code' => $countryIsoCode,
    ]);
  }

  abstract public function getShippingAddress(): RiskifiedAddress;
  abstract public function getBillingAddress(): RiskifiedAddress;
  abstract public function getLineItems(string $fulfillmentStatus = null): array;
  abstract public function getCheckoutData(RiskifiedData $data): RiskifiedOrder;
  abstract public function getPaymentGateway(): string;
}
