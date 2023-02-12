<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/subscribe', function() {
        return view('subscribe', [
            'intent' => Auth::user()->createSetupIntent(),
        ]);
    })->name('subscribe'); 

    Route::post('/subscribe', function(Request $request) {
        Auth::user()->newSubscription('default', $request->plan)->create($request->payment_method);
        Log::debug('created new subscription!');

        return redirect(route('subscribers'));
    })->name('subscribe.post');

    Route::get('/subscribers', function(Request $request) {
        return view('subscribers');
    })->middleware('subscribers')->name('subscribers');

    Route::get('/charge', function() {
        return view('single-charge', [
            'invoices' => Auth::user()->invoices(),
        ]);
    })->name('charge'); 

    Route::post('/charge', function(Request $request) {
        try {
            // createAsStripeCustomer() will cause error "Customer already created" after first time
            Auth::user()->createOrGetStripeCustomer();

            if (! Auth::user()->hasDefaultPaymentMethod()) {
                Auth::user()->updateDefaultPaymentMethod($request->payment_method);
            }

            // invoicePrice(stripe product id, quantities)
            // invoiceFor(description, amount)
            // charge(amount, payment_method)
            Auth::user()->invoicePrice('price_1MAunGK3AEGA3BHpoNvsf1Fd', 100); 

            return back()->with('success', 'Payment successful');
        } catch (Exception $e) {;
            Log::error($e->getMessage());

            return back()->with('error', $e->getMessage());
        }
    })->name('charge.post');

    Route::get('/user/invoice/{invoice}', function (Request $request, $invoiceId) {
        return $request->user()->downloadInvoice($invoiceId, [
            'vendor' => 'Your Company',
            'product' => 'Your Product',
            'street' => 'Main Str. 1',
            'location' => '2000 Antwerp, Belgium',
            'phone' => '+32 499 00 00 00',
            'email' => 'info@example.com',
            'url' => 'https://example.com',
            'vendorVat' => 'BE123456789',
        ]);
    });
});

require __DIR__.'/auth.php';
