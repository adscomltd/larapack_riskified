<?php

namespace Adscom\LarapackRiskified\DataTransferObjects;

use App\Models\Order;
use PaymentManager;
use Riskified\OrderWebhook\Model;

class DecideData extends RiskifiedData
{
  /**
   * @param Order $orderModel
   * @return Model\Order
   * @throws \Exception
   */
  public function parse(Order $orderModel): Model\Order
  {
    $order = (new CheckoutData)->parse($orderModel);

    $order->billing_address = $this->getBillingAddress($orderModel);

    $order->gateway = PaymentManager::getDefaultDriver();

    return $order;
  }
}
