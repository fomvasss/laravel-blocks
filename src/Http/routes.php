<?php

use Illuminate\Support\Facades\Route;

Route::get('/blocks-imagecache/{path}', [\Fomvasss\Blocks\Http\ImagecacheController::class, 'imagecache'])
    ->where('path', '.*')
    ->name('imagecache');
