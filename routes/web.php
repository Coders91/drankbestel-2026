<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\InvoiceController;
use App\Livewire\Cart;
use App\Livewire\Checkout;
use App\Livewire\Contact;
use App\Livewire\Favorites;
use App\Livewire\MyAccount;
use App\Livewire\ProductReviewPage;
use App\Livewire\SiteSearch;

// Livewire page components
Route::get('/winkelwagen/', Cart::class)->name('cart');
Route::get('/afrekenen/', Checkout::class)->name('checkout');
Route::get('/favorieten/', Favorites::class)->name('favorites');
Route::get('/account/', MyAccount::class)->name('account');
Route::get('/contact/', Contact::class)->name('contact');

// Checkout payment flow
Route::get('/afrekenen/betaling/{order_id}/', [CheckoutController::class, 'paymentReturn'])->name('payment.return');
Route::get('/afrekenen/bedankt/{order_id}/', [CheckoutController::class, 'thankYou'])->name('thankyou');

// Invoice routes (admin only)
Route::get('/factuur/{order_id}/preview/', [InvoiceController::class, 'preview'])->name('invoice.preview');
Route::get('/factuur/{order_id}/download/', [InvoiceController::class, 'download'])->name('invoice.download');

// Search
Route::get('/zoeken/', SiteSearch::class)->name('search');

// Product review page (for email campaigns)
Route::get('/{product_slug}/beoordeling', ProductReviewPage::class)->name('review');
