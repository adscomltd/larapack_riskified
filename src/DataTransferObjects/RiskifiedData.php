<?php

namespace Adscom\LarapackRiskified\DataTransferObjects;

use Adscom\LarapackRiskified\Contracts\Order;
use Adscom\LarapackRiskified\Services\Riskified;
use App\Utilities\Cookie;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Riskified\OrderWebhook\Model\Order as RiskifiedOrder;

abstract class RiskifiedData
{
  private Agent $agent;

  abstract public function parse(Order $order): RiskifiedOrder;

  public function __construct()
  {
    $this->agent = new Agent();
  }

  public function getSource(): string
  {
    return $this->agent->isMobile() ? 'mobile_web' : 'desktop_web';
  }

  public function formatted_date($date = null): string
  {
    if (empty($date)) {
      $date = time();
    }
    return date('Y-m-d\TH:i:sO', $date);
  }

  /**
   * Get beacon session id created on frontend side and stored in cookie
   * or create it if it doesn't exist
   * @return string
   */
  public function getCardToken(): string
  {
    $token = Cookie::get(Riskified::RISKIFIED_BEACON_KEY);
    if (!isset($token)) {
      $token = Str::random(8);
      Cookie::save(Riskified::RISKIFIED_BEACON_KEY, $token);
    }

    return $token;
  }
}
