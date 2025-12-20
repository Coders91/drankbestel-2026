<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CheckoutController;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Favorites;
use App\Livewire\MyAccount;

// Livewire page components
Route::get('/winkelwagen/', Cart::class)->name('cart');
Route::get('/afrekenen/', Checkout::class)->name('checkout');
Route::get('/favorieten/', Favorites::class)->name('favorites');
Route::get('/account/', MyAccount::class)->name('account');

// Checkout payment flow
Route::get('/afrekenen/betaling/{order_id}/', [CheckoutController::class, 'paymentReturn'])->name('payment.return');
Route::get('/afrekenen/bedankt/{order_id}/', [CheckoutController::class, 'thankYou'])->name('thankyou');
