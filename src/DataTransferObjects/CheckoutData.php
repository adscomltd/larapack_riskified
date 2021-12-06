<?php

namespace Adscom\LarapackRiskified\DataTransferObjects;

use Adscom\LarapackRiskified\Contracts\Order;
use Riskified\OrderWebhook\Model\Order as RiskifiedOrder;

class CheckoutData extends RiskifiedData
{
  /**
   * @param  Order  $order
   * @return RiskifiedOrder
   */
  public function parse(Order $order): RiskifiedOrder
  {
    return $order->getCheckoutData($this);
  }
}
