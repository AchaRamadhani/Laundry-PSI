<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use Barryvdh\DomPDF\ServiceProvider as DompdfServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    DompdfServiceProvider::class,
];
