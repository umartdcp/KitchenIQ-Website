<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderWizardController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/menu', [HomeController::class, 'menu'])->name('menu');
Route::get('/plans', [HomeController::class, 'plans'])->name('plans');
Route::get('/how-it-works', [HomeController::class, 'how'])->name('how');
Route::get('/catering', [HomeController::class, 'catering'])->name('catering');
Route::get('/about', [HomeController::class, 'about'])->name('about');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/order/menu', [OrderWizardController::class, 'menu'])->name('order.menu');
    Route::post('/order/menu', [OrderWizardController::class, 'storeMenu'])->name('order.menu.store');
    Route::get('/order/schedule', [OrderWizardController::class, 'schedule'])->name('order.schedule');
    Route::post('/order/schedule', [OrderWizardController::class, 'storeSchedule'])->name('order.schedule.store');
    Route::get('/order/packing', [OrderWizardController::class, 'packing'])->name('order.packing');
    Route::post('/order/packing', [OrderWizardController::class, 'storePacking'])->name('order.packing.store');
    Route::get('/order/delivery', [OrderWizardController::class, 'delivery'])->name('order.delivery');
    Route::post('/order/delivery', [OrderWizardController::class, 'storeDelivery'])->name('order.delivery.store');
    Route::get('/order/review', [OrderWizardController::class, 'review'])->name('order.review');
    Route::post('/order/pay', [OrderWizardController::class, 'pay'])->name('order.pay');
    Route::get('/order/confirmed/{order}', [OrderWizardController::class, 'confirmed'])->name('order.confirmed');

    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/deliveries/{delivery}', [CustomerOrderController::class, 'updateDelivery'])->name('orders.delivery.update');
    Route::post('/orders/{order}/extend', [CustomerOrderController::class, 'extend'])->name('orders.extend');
    Route::post('/orders/{order}/pay-cycle', [CustomerOrderController::class, 'payCycle'])->name('orders.pay-cycle');
});
