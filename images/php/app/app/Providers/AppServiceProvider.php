<?php

namespace App\Providers;

use App\Interfaces\ModelTransactionInterface;
use App\Interfaces\ModelWalletInterface;
use App\Interfaces\ShopkeeperRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\WalletRepositoryInterface;
use App\Repositories\ShopkeeperRepository;
use App\Repositories\UserRepository;
use App\Repositories\WalletRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class,
            ShopkeeperRepositoryInterface::class,
            ShopkeeperRepository::class,
            WalletRepository::class,
        );
    }
}
