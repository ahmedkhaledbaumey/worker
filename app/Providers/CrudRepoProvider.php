<?php

namespace App\Providers;

use App\Repositries\ClientOrderRepo;

use App\Interfaces\CrudRepoInterface;


use Illuminate\Support\ServiceProvider;
use App\Http\Controllers\ClientOrderController;
use App\Interfaces\CrudRepottttttttInterface\CrudRepottttttttInterface;
use Illuminate\Contracts\Filesystem\Filesystem;

class CrudRepoProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // $this->app->bind(CrudRepoInterface::class, ClientorderRepo::class);

        $this->app->when(ClientOrderController::class)
            ->needs(CrudRepoInterface::class)

            ->give(function () {
                return new ClientOrderRepo();
            });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
