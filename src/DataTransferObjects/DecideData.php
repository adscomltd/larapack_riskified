<?php

namespace Adscom\LarapackRiskified\DataTransferObjects;

use Adscom\LarapackRiskified\Contracts\Order;
use Riskified\OrderWebhook\Model\Order as RiskifiedOrder;

class DecideData extends RiskifiedData
{
  /**
   * @param  Order  $order
   * @return RiskifiedOrder
   */
  public function parse(Order $order): RiskifiedOrder
  {
    $riskifiedOrder = $order->getCheckoutData($this);

    $riskifiedOrder->billing_address = $order->getBillingAddress();

    $order->gateway = $order->getPaymentGateway();

    return $riskifiedOrder;
  }
}
