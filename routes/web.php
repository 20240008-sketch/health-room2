<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('app');
});

// SPA用のcatch-allルート - Vue.jsがルーティングを処理
Route::get('/{path?}', function () {
    return view('app');
})->where('path', '.*');
