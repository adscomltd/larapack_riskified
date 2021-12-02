<?php

namespace Adscom\LarapackRiskified\Exceptions;

use App\Models\Order;
use Adscom\LarapackRiskified\Models\RiskifiedLog;
use Exception;
use Illuminate\Support\Facades\Log;

class RiskifiedException extends Exception
{

  public function __construct(
    protected Exception    $exception,
    protected RiskifiedLog $riskifiedLog,
    protected Order        $order,
    protected string       $apiName
  )
  {
    parent::__construct($this->exception->getMessage(), $this->exception->getCode());
  }

  public function report()
  {
    // logic changed we need to put accept if get 400 from riskified
    $this->riskifiedLog->status = RiskifiedLog::STATUS_EXCEPTION;
    $this->riskifiedLog->response = [
      "code" => $this->exception->getCode(),
      "message" => $this->exception->getMessage()
    ];
    $this->riskifiedLog->save();

    Log::error(
      "Riskified: exception on {$this->apiName}",
      [
        'order_id' => $this->order->id,
        'exception_message' => $this->exception->getMessage()
      ]
    );

    if (app()->isProduction()) {
      // @todo add alert into slack
    }
  }
}
