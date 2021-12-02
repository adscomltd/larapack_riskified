<?php

namespace Adscom\LarapackRiskified\DataTransferObjects;

use App\Models\Order;
use Riskified\OrderWebhook\Model;

class CheckoutData extends RiskifiedData
{
  /**
   * @param Order $orderModel
   * @return Model\Order
   * @throws \Exception
   */
  public function parse(Order $orderModel): Model\Order
  {
    $order = new Model\Order([
      'id' => (string) $orderModel->uuid,
      'email' => $orderModel->customer->email,
      'source' => $this->getSource(),
      'created_at' => $this->formatted_date(), // create only first time
      'updated_at' => $this->formatted_date(),
      'currency' => $orderModel->processed_currency,
      'browser_ip' => $orderModel->ip,
      'total_price' => $orderModel->due_amount,
      'cart_token' => $this->getCardToken(),
      'vendor_name' => 'hyperstech.com', //parse_url(config('app.url'), PHP_URL_HOST)
    ]);

    if ($orderModel->network) {
      $order->vendor_id = $orderModel->network;
      $order->submission_reason = 'third_party';
    }

    $order->shipping_address = $this->getShippingAddress($orderModel);

    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
      $order->client_details = new Model\ClientDetails([
        "accept_language" => $_SERVER['HTTP_ACCEPT_LANGUAGE'],
        "user_agent" => $_SERVER['HTTP_USER_AGENT']
      ]);
    }

    $order->shipping_lines = [new Model\ShippingLine([
      'price' => $orderModel->shipping_data['price'],
      'title' => $orderModel->shipping_data['method_name']
    ])];

    $order->total_discounts = 0;
    $order->line_items = $this->getLineItems($orderModel);

    return $order;
  }
}
