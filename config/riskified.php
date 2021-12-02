<?php

return [
  'enabled' => env('RISKIFIED_ENABLED', true),
  'shadowMode' => env('RISKIFIED_SHADOW_MODE'),

  'company' => env('RISKIFIED_COMPANY'),
  'api_key' => env('RISKIFIED_API_KEY'),
  'order_id_prefix' => env('RISKIFIED_ORDER_ID_PREFIX')
];
