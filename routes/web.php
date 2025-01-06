<?php

use App\Http\Controllers\BookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// 绑定了之后会自动create相关路由
Route::resource('books', BookController::class);
