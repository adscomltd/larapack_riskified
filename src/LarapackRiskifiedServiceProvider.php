<?php

namespace Adscom\LarapackRiskified;

use Illuminate\Support\ServiceProvider;
use Riskified\Common\Env as RiskifiedEnv;
use Riskified\Common\Riskified as RiskifiedClient;
use Riskified\Common\Signature\HttpDataSignature;
use Riskified\OrderWebhook\Transport\CurlTransport;
use Adscom\LarapackRiskified\Services\Riskified;


class LarapackRiskifiedServiceProvider extends ServiceProvider
{
  public function register()
  {
    //
  }

  public function boot()
  {
    // using in Riskified service, for sending requests to riskified
    $this->app->singleton('RiskifiedClient', function () {
      RiskifiedClient::init(
        config('riskified.company'),
        config('riskified.api_key'),
        app()->isProduction() ? RiskifiedEnv::PROD : RiskifiedEnv::SANDBOX
      );

      return new CurlTransport(new HttpDataSignature());
    });

    // Riskified service
    $this->app->singleton(Riskified::class, Riskified::class);

    if ($this->app->runningInConsole()) {
      $this->publishes([
        __DIR__.'/../config/riskified.php' => config_path('riskified.php'),
      ], 'config');

      // Export the migration
      if (!class_exists('CreateRiskifiedLogsTable')) {
        $this->publishes([
          __DIR__.'/../database/migrations/create_riskified_logs_table.php.stub' => database_path('migrations/'.date('Y_m_d_His',
              time()).'_create_riskified_logs_table.php'),
          // you can add any number of migrations here
        ], 'migrations');
      }
    }
  }
}
