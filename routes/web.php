<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\ManagerApprovalController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PlatformController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\StoreController as AdminStoreController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CustomerCouponController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Route;

// ── Public landing ──────────────────────────────────────────────────────────
Route::get('/', fn () => redirect()->route('login'));

// ── Customer / authenticated routes ─────────────────────────────────────────
Route::middleware(['auth'])->group(function () {
    // Default dashboard: customers → shop, managers/admins → admin dashboard
    Route::get('/dashboard', function () {
        return auth()->user()->isManager()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('shop.index');
    })->name('dashboard');

    // ── Shop ───────────────────────────────────────────────────────────────
    Route::get('/shop',                  [ShopController::class, 'index'])->name('shop.index');
    Route::get('/shop/store/{slug}',     [ShopController::class, 'store'])->name('shop.store');
    Route::get('/shop/product/{id}',     [ShopController::class, 'product'])->name('shop.product');

    // ── Cart ───────────────────────────────────────────────────────────────
    Route::get('/cart',                  [CartController::class, 'show'])->name('cart.show');
    Route::post('/cart/add',             [CartController::class, 'add'])->name('cart.add');
    Route::patch('/cart/update',         [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/{productId}',   [CartController::class, 'destroy'])->name('cart.destroy');
    Route::post('/cart/clear',           [CartController::class, 'clear'])->name('cart.clear');

    // ── Checkout ───────────────────────────────────────────────────────────
    Route::get('/checkout',              [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/coupon',      [CheckoutController::class, 'applyCoupon'])
        ->name('checkout.coupon')->middleware('throttle:30,1');
    Route::delete('/checkout/coupon',    [CheckoutController::class, 'removeCoupon'])->name('checkout.coupon.remove');
    Route::post('/checkout/place',       [CheckoutController::class, 'place'])
        ->name('checkout.place')->middleware('throttle:10,1');

    // ── Orders ─────────────────────────────────────────────────────────────
    Route::get('/orders',                [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}',           [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/reorder',  [OrderController::class, 'reorder'])->name('orders.reorder');

    // ── Coupons hub (browse + my savings + redemption history) ────────────
    Route::get('/coupons',               [CustomerCouponController::class, 'index'])->name('coupons.index');

    // ── Wishlist ───────────────────────────────────────────────────────────
    Route::get('/wishlist',                       [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/toggle',               [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::delete('/wishlist/{productId}',        [WishlistController::class, 'destroy'])->name('wishlist.destroy');

    // ── Product reviews ────────────────────────────────────────────────────
    Route::post('/reviews/{productId}',           [ReviewController::class, 'store'])->name('reviews.store');
    Route::delete('/reviews/{reviewId}',          [ReviewController::class, 'destroy'])->name('reviews.destroy');

    // ── Notifications (in-app) ─────────────────────────────────────────────
    Route::get('/notifications',                  [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/bell',             [NotificationController::class, 'bell'])->name('notifications.bell');
    Route::post('/notifications/read-all',        [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('/notifications/{id}/read',       [NotificationController::class, 'markRead'])->name('notifications.markRead');

    // ── Legacy coupon-validate endpoint (still used by older flows) ───────
    Route::get('/coupon',                [CouponController::class, 'show'])->name('coupon.show');
    Route::post('/coupon/validate',      [CouponController::class, 'validate'])
        ->name('coupon.validate')->middleware('throttle:30,1');
    Route::post('/coupon/apply',         [CouponController::class, 'apply'])
        ->name('coupon.apply')->middleware('throttle:5,1');

    // Legacy /deals → redirect to /shop (deals are now embedded in store pages)
    Route::get('/deals', fn () => redirect()->route('shop.index'))->name('deals');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin / Manager routes ──────────────────────────────────────────────────
Route::middleware(['auth', 'manager'])->prefix('admin')->name('admin.')->group(function () {

    // Store registration is the ONE thing a manager can do without already having a store
    Route::get('/store/create',     [AdminStoreController::class, 'create'])->name('store.create');
    Route::post('/store',           [AdminStoreController::class, 'store'])->name('store.store');

    // Super-admin-only: review pending manager applications
    Route::get('/approvals',                       [ManagerApprovalController::class, 'index'])->name('approvals.index');
    Route::post('/approvals/{id}/approve',         [ManagerApprovalController::class, 'approve'])->name('approvals.approve');
    Route::delete('/approvals/{id}',               [ManagerApprovalController::class, 'reject'])->name('approvals.reject');

    // Everything else requires the manager to have a registered store
    Route::middleware('require.store')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // ── Store management ───────────────────────────────────────────────
        Route::get('/store',       [AdminStoreController::class, 'show'])->name('store.show');
        Route::get('/store/edit',  [AdminStoreController::class, 'edit'])->name('store.edit');
        Route::patch('/store',     [AdminStoreController::class, 'update'])->name('store.update');

        // ── Products ───────────────────────────────────────────────────────
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::patch('/products/{product}/toggle', [AdminProductController::class, 'toggle'])->name('products.toggle');

        // ── Discounts ──────────────────────────────────────────────────────
        Route::resource('discounts', DiscountController::class);
        Route::patch('/discounts/{discount}/toggle',    [DiscountController::class, 'toggle'])->name('discounts.toggle');
        Route::post('/discounts/{discount}/duplicate',  [DiscountController::class, 'duplicate'])->name('discounts.duplicate');

        // ── Promotions ─────────────────────────────────────────────────────
        Route::resource('promotions', PromotionController::class);
        Route::patch('/promotions/{promotion}/toggle', [PromotionController::class, 'toggle'])->name('promotions.toggle');

        // ── Orders (manager fulfillment) ───────────────────────────────────
        Route::get('/orders',                       [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}',                  [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{id}/status',         [AdminOrderController::class, 'updateStatus'])->name('orders.status');

        // ── Analytics ──────────────────────────────────────────────────────
        Route::get('/analytics',        [AnalyticsController::class, 'index'])->name('analytics');
        Route::get('/analytics/export', [AnalyticsController::class, 'export'])->name('analytics.export');

        // ── Super-admin platform-wide pages ────────────────────────────────
        Route::get('/platform/stores',                 [PlatformController::class, 'stores'])->name('platform.stores');
        Route::patch('/platform/stores/{id}/toggle',   [PlatformController::class, 'toggleStore'])->name('platform.stores.toggle');
        Route::patch('/platform/stores/{id}/feature',  [PlatformController::class, 'toggleFeatured'])->name('platform.stores.feature');
        Route::get('/platform/users',                  [PlatformController::class, 'users'])->name('platform.users');
        Route::get('/platform/orders',                 [PlatformController::class, 'orders'])->name('platform.orders');
    });
});

require __DIR__.'/auth.php';
