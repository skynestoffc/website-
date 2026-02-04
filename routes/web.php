use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Webhook\PakasirWebhookController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\StockController as AdminStockController;

Route::get('/', [StorefrontController::class, 'index'])->name('home');
Route::get('/p/{slug}', [StorefrontController::class, 'show'])->name('product.show');

Route::post('/checkout/{product:slug}', [CheckoutController::class, 'checkout'])
    ->name('checkout');

Route::get('/orders/{order_code}', [OrderController::class, 'show'])
    ->name('orders.show');

// Webhook Pakasir
Route::post('/webhooks/pakasir', [PakasirWebhookController::class, 'handle'])
    ->name('webhooks.pakasir');

// Admin (pakai auth + is_admin middleware sesuai implementasi kamu)
Route::prefix('admin')->middleware(['auth','is_admin'])->group(function () {
    Route::resource('products', AdminProductController::class);
    Route::get('products/{product}/stocks', [AdminStockController::class, 'index'])->name('admin.stocks.index');
    Route::post('products/{product}/stocks', [AdminStockController::class, 'store'])->name('admin.stocks.store');
    Route::delete('stocks/{stock}', [AdminStockController::class, 'destroy'])->name('admin.stocks.destroy');
});
