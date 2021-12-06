<?php

namespace Adscom\LarapackRiskified\Services;

use Adscom\LarapackRiskified\Contracts\Order;
use Adscom\LarapackRiskified\Events\RiskifiedApproved;
use Adscom\LarapackRiskified\Events\RiskifiedDeclined;
use Adscom\LarapackRiskified\Models\RiskifiedLog;
use Adscom\LarapackRiskified\Exceptions\RiskifiedException;
use Riskified\OrderWebhook\Model\Order as RiskifiedOrder;
use Exception;
use Log;
use Riskified\OrderWebhook\Transport\CurlTransport;
use Str;

/**
 * test@approve.com - for getting always approve
 * test@decline.com - for getting always decline
 */
class Riskified
{
  public const RISKIFIED_BEACON_KEY = 'riskified_beacon_sid';

  protected CurlTransport $client;

  protected array $steps = [
    RiskifiedLog::STAGE_CHECKOUT => [
      'name' => 'checkout',
      'api_call_name' => '/api/checkout_create',
      'method_name' => 'createCheckout',
      'response_name' => 'checkout'
    ],
    RiskifiedLog::STAGE_DECIDE => [
      'name' => 'decide',
      'api_call_name' => '/api/decide',
      'method_name' => 'decideOrder',
      'response_name' => 'order'
    ],
    'denied' => [
      'name' => 'denied',
      'api_call_name' => '/api/checkout_denied',
      'method_name' => 'deniedCheckout',
      'response_name' => 'checkout'
    ],
    'decision' => [
      'name' => 'decision',
      'api_call_name' => '/api/decision',
      'method_name' => 'decisionOrder',
      'response_name' => 'order'
    ],
    'cancel' => [
      'name' => 'cancel',
      'api_call_name' => '/api/cancel',
      'method_name' => 'cancelOrder',
      'response_name' => 'order'
    ],
    'refund' => [
      'name' => 'refund',
      'api_call_name' => '/api/refund',
      'method_name' => 'refundOrder',
      'response_name' => 'order'
    ],
    'chargeback' => [
      'name' => 'chargeback',
      'api_call_name' => '/api/chargeback',
      'method_name' => 'chargebackOrder',
      'response_name' => 'order'
    ],
  ];

  public function __construct()
  {
    $this->client = app('RiskifiedClient');
    // set timeout
    $this->client->timeout = 30;
  }

  /**
   * Checking need to send request to riskified or not
   * @return bool
   */
  public static function isEnabled(): bool
  {
    return config('riskified.enabled');
  }

  /**
   * Call for creating order on riskified
   * @param  Order  $order
   * @return string
   * @throws RiskifiedException
   */
  public function checkout(Order $order): string
  {
    if (self::isEnabled()) {
      $this->request(RiskifiedLog::STAGE_CHECKOUT, $order);
    }

    return $order->getUuid();
  }

  /**
   * Call for creating order on riskified
   * @param  Order  $order
   * @return bool
   * @throws RiskifiedException
   */
  public function decide(Order $order): bool
  {
    $status = true;

    if (self::isEnabled()) {
      $status = (bool) $this->request(RiskifiedLog::STAGE_DECIDE, $order)->status;

      if (!$status) {
        // declined
        $this->onDecline($order);
      } else {
        // approved
        $this->onApprove($order);
      }
    }

    return $status;
  }

  public function onDecline(Order $order): void
  {
    event(new RiskifiedDeclined($order));
  }

  public function onApprove(Order $order): void
  {
    event(new RiskifiedApproved($order));
  }

  /**
   * Sending api request and log sending data and response
   * @param  int  $stage
   * @param  Order  $order
   * @return RiskifiedLog
   * @throws RiskifiedException
   */
  private function request(int $stage, Order $order): RiskifiedLog
  {
    /** @var RiskifiedLog $riskifiedLog */
    $riskifiedLog = $order->riskifiedLogs()->create([
      'stage' => $stage,
    ]);

    try {
      $data = $this->prepareDataForRequest($stage, $order);

      // api call
      $response = call_user_func([$this->client, $this->steps[$stage]['method_name']], $data);

      // log response
      Log::info(
        "Riskified: send {$this->steps[$stage]['api_call_name']}",
        [
          'order_id' => $order->getUuid(),
          'request_data' => $data->toJson(),
          'response' => $response
        ]
      );

      // save riskifiedLog data
      $riskifiedLog->updateAfterRequest(
        $response,
        $response->{$this->steps[$stage]['response_name']}->status,
      );
    } catch (Exception $e) { // exceptions from riskified
      throw new RiskifiedException(
        $e, $riskifiedLog, $order, $this->steps[$stage]['api_call_name']
      );
    }


    return $riskifiedLog;
  }

  /**
   * Prepare data for riskified SDK to send request
   * @param  int  $stage
   * @param  Order  $order
   * @return mixed
   */
  private function prepareDataForRequest(int $stage, Order $order): RiskifiedOrder
  {
    $dtoClassName = ucfirst($this->steps[$stage]['name']).'Data';
    $namespace = Str::of(__NAMESPACE__)->beforeLast('\\').'\\DataTransferObjects';

    $dtoClass = $namespace.'\\'.$dtoClassName;

    return (new $dtoClass)->parse($order);
  }
}
