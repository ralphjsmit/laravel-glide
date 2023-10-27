<?php

use Illuminate\Support\Facades\Route;
use RalphJSmit\Laravel\Glide\Http\Controllers\GlideController;

Route::get('glide/{source}', GlideController::class)
    ->where('source', '.*')
    ->name('glide.generate');
