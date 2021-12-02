<?php

namespace Adscom\LarapackRiskified\DataTransferObjects;

use Adscom\LarapackRiskified\Services\Riskified;
use App\Models\Order;
use App\Utilities\Cookie;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Riskified\OrderWebhook\Model;

abstract class RiskifiedData
{
  private Agent $agent;

  abstract public function parse(Order $order): Model\Order;

  public function __construct()
  {
    $this->agent = new Agent();
  }

  protected function getSource(): string
  {
    return $this->agent->isMobile() ? 'mobile_web' : 'desktop_web';
  }

  /**
   * @param Order $order
   * @param null $fulfillmentStatus
   * @return array
   */
  protected function getLineItems(Order $order, $fulfillmentStatus = null): array
  {
    $lineItems = [];

    return $order
      ->lineItems()
      ->with(['product'])
      ->get()
      ->map(function ($item) use (&$totalSum, $fulfillmentStatus) {
        $lineItem = new Model\LineItem([
          "title" => $item->product->name,
          "price" => $item->price * $item->rate,
          "quantity" => $item->qty,
          "product_id" => (string)$item->product_id,
          "product_type" => 'physical', // 'digital',
        ]);

        if ($fulfillmentStatus) {
          $lineItem->fulfillment_service = 'manual';
          $lineItem->fulfillment_status = $fulfillmentStatus;
        } else {
          $lineItem->requires_shipping = true;
        }

        return $lineItem;
      })
      ->toArray();
  }

  public function getShippingAddress(Order $order): Model\Address
  {
    $address = $this->getAddress($order);
    $orderAddress = $order->shippingAddress;

    $address->address1 = $order->shippingAddress->address_line_1;
    $address->city = $orderAddress->city;
    $address->province = $orderAddress->state;
    $address->zip = $orderAddress->zip_code;

    return $address;
  }

  public function getBillingAddress(Order $order): Model\Address
  {
    $address = $this->getAddress($order);
    $orderAddress = $order->shippingAddress;

    $address->phone = $orderAddress->phone;
    $address->first_name = $orderAddress->first_name;
    $address->last_name = $orderAddress->last_name;

    return $address;
  }

  /**
   * Get Address For customer
   * @param Order $order
   * @return Model\Address
   * @throws \Exception
   */
  private function getAddress(Order $order): Model\Address
  {
    $orderAddress = $order->shippingAddress;

    $country = $orderAddress->country;

    $address = new Model\Address([
      "country_code" => optional($country)->iso
    ]);

    return $address;
  }

  protected function formatted_date($date = null): string
  {
    if (empty($date)) {
      $date = time();
    }
    return date('Y-m-d\TH:i:sO', $date);
  }

  /**
   * Get beacon session id created on frontend side and stored in cookie
   * or create it if it doesn't exists
   * @return string
   */
  protected function getCardToken(): string
  {
    $token = Cookie::get(Riskified::RISKIFIED_BEACON_KEY);
    if (!isset($token)) {
      $token = Str::random(8);
      Cookie::save(Riskified::RISKIFIED_BEACON_KEY, $token);
    }

    return $token;
  }
}
