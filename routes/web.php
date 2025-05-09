<?php

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});



Route::get('/', [HomeController::class , "index"])->middleware("auth");

Route::group(["prefix" => "admin"] , function(){
    Route::resource("users" , UserController::class);
    Route::get("users.updateColumnSelected" , [UserController::class , "updateColumnSelected"])->name("users.updateColumnSelected");
});













Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});


Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');
