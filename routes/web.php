<?php

use Illuminate\Support\Facades\Route;
use RalphJSmit\Laravel\Glide\Http\Controllers\GlideController;

$route = Route::get('glide/{source}', GlideController::class)
    ->where('source', '.*')
    ->name('glide.generate');

if ($domain = config('glide.route.domain')) {
    $route->domain($domain);
}
