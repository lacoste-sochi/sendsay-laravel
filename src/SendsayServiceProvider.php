<?php

namespace Rutrue\Sendsay;

use Illuminate\Support\ServiceProvider;
use Rutrue\Sendsay\Contracts\SendsayClientInterface;
use Rutrue\Sendsay\Contracts\SendsayServiceInterface;
use Rutrue\Sendsay\Notifications\SendsayChannel;
use Rutrue\Sendsay\Services\SendsayClient;
use Rutrue\Sendsay\Services\SendsayService;

class SendsayServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/sendsay.php', 'sendsay');

        $this->app->singleton(SendsayClientInterface::class, function ($app) {
            return new SendsayClient(
                config('sendsay.account'),
                config('sendsay.api_key'),
                config('sendsay.base_url')
            );
        });

        $this->app->singleton(SendsayServiceInterface::class, function ($app) {
            return new SendsayService(
                $app->make(SendsayClientInterface::class),
                config('sendsay.default_from'),
            );
        });

        // Явно регистрируем канал, иначе не успевает зарегистрироваться если вызвать явно через app
        $this->app->singleton(SendsayChannel::class, function ($app) {
            return new SendsayChannel(
                $app->make(SendsayServiceInterface::class)
            );
        });

        $this->app->alias(SendsayServiceInterface::class, 'sendsay');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/sendsay.php' => config_path('sendsay.php'),
        ], 'sendsay-config');

    }
}
