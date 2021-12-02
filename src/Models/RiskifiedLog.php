<?php

namespace Adscom\LarapackRiskified\Models;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RiskifiedLog extends Model
{
  public const STAGE_CHECKOUT = 1;
  public const STAGE_DECIDE = 2;
  public const STAGE_DECISION = 3;
  public const STAGE_DENIED = 4;
  public const STAGE_CANCEL = 5;
  public const STAGE_REFUND = 6;
  public const STAGE_CHARGEBACK = 7;

  public const STATUS_DECLINED = 0;
  public const STATUS_APPROVED = 1;
  public const STATUS_CAPTURED = 2;
  public const STATUS_EXCEPTION = 3;

  protected array $statuses = [
    'declined' => RiskifiedLog::STATUS_DECLINED,
    'approved' => RiskifiedLog::STATUS_APPROVED,
    'captured' => RiskifiedLog::STATUS_CAPTURED,
  ];

  protected $fillable = [
    'order_id',
    'stage',
    'status',
    'response_time',
    'response',
  ];

  public $casts = [
    'response' => 'array',
  ];

  public function order(): HasOne
  {
    return $this->hasOne(Order::class, 'id', 'order_id');
  }

  public function updateAfterRequest($response, $status): bool
  {
    $this->status = $this->statuses[$status] ?? null;
    $this->response = $response;
    return $this->save();
  }
}
