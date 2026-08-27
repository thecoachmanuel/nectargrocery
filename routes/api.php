<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\MasterController;
use App\Http\Controllers\API\HomeController;
use App\Http\Controllers\API\BannerController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ShopController;
use App\Http\Controllers\API\FlashSaleController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\AddressController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\LegalPageController;
use App\Http\Controllers\API\SupportController;
use App\Http\Controllers\API\SupportTicketController;
use App\Http\Controllers\API\SupportTicketMessageController;
use App\Http\Controllers\API\TicketIssueTypeController;
use App\Http\Controllers\API\CountryController;
use App\Http\Controllers\API\ChatController;

use App\Http\Controllers\API\UserController as ApiUserController;
use App\Http\Controllers\API\Auth\AuthController as ApiAuthController;
use App\Http\Controllers\API\Auth\ForgotPasswordController as ApiForgotPasswordController;
use App\Http\Controllers\API\Rider\NotificationController as RiderNotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Master Data
Route::get('/master', [MasterController::class, 'index']);

// Home
Route::get('/home', [HomeController::class, 'index']);
Route::get('/popular-products', [HomeController::class, 'popularProducts']);
Route::get('/recently-views', [HomeController::class, 'recentlyViews']);

// Banners
Route::get('/banners', [BannerController::class, 'index']);

// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::post('/products/{id}/favorite', [ProductController::class, 'addFavorite']);
Route::get('/products/{id}/reviews', [ProductController::class, 'reviews']);
Route::post('/products/{id}/reviews', [ProductController::class, 'storeReview']);

// Shops
Route::get('/shops', [ShopController::class, 'index']);
Route::get('/shops/{id}', [ShopController::class, 'show']);

// Flash Sales
Route::get('/flash-sales', [FlashSaleController::class, 'index']);
Route::get('/flash-sales/{id}', [FlashSaleController::class, 'show']);

// Coupons
Route::get('/coupons', [CouponController::class, 'index']);
Route::post('/coupons/apply', [CouponController::class, 'apply']);

// Cart & Checkout
Route::get('/carts', [CartController::class, 'index']);
Route::post('/cart/store', [CartController::class, 'store']);
Route::post('/cart/increment', [CartController::class, 'increment']);
Route::post('/cart/decrement', [CartController::class, 'decrement']);
Route::post('/cart/delete', [CartController::class, 'destroy']);
Route::post('/cart/checkout', [CartController::class, 'checkout']);
Route::post('/place-order', [OrderController::class, 'store']);

// Auth
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);
Route::post('/forgot-password', [ApiForgotPasswordController::class, 'sendOtp']);
Route::post('/verify-otp', [ApiForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [ApiForgotPasswordController::class, 'resetPassword']);

// Authenticated API User Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::get('/profile', [ApiUserController::class, 'index']);
    Route::post('/profile/update', [ApiUserController::class, 'update']);
    Route::post('/profile/password', [ApiUserController::class, 'updatePassword']);

    // Orders
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);

    // Addresses
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{id}', [AddressController::class, 'update']);
    Route::delete('/addresses/{id}', [AddressController::class, 'destroy']);

    // Notifications
    Route::get('/notifications', [RiderNotificationController::class, 'index']);
    Route::get('/notifications/{id}/read', [RiderNotificationController::class, 'read']);

    // Support Tickets
    Route::get('/support-tickets', [SupportTicketController::class, 'index']);
    Route::post('/support-tickets', [SupportTicketController::class, 'store']);
    Route::get('/support-tickets/{id}', [SupportTicketController::class, 'show']);
    Route::post('/support-tickets/{id}/messages', [SupportTicketMessageController::class, 'store']);

    // Wishlist / Favorite
    Route::post('/favorite-add-or-remove', [ProductController::class, 'addFavorite']);
    Route::get('/favorite-products', [ProductController::class, 'favoriteProducts']);
});

// Legal Pages, Countries, Blogs, Translations
Route::get('/lang/{locale}', function ($locale) {
    $path = lang_path($locale . '.json');
    if (file_exists($path)) {
        return response()->json(json_decode(file_get_contents($path), true));
    }
    return response()->json([]);
});
Route::get('/update-last-seen', function () {
    if (auth()->check()) {
        auth()->user()->update(['last_online' => now()]);
    }
    return response()->json(['status' => true]);
});
Route::get('/legal-pages/{slug}', [LegalPageController::class, 'show']);
Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'show']);
Route::get('/countries', [CountryController::class, 'index']);
Route::get('/ticket-issue-types', [TicketIssueTypeController::class, 'index']);

// Chat & Messaging
Route::post('/store-message', [ChatController::class, 'storeMessage']);
Route::get('/get-shops', [ChatController::class, 'getShops']);
Route::get('/get-users', [ChatController::class, 'getUsers']);
Route::get('/get-messages', [ChatController::class, 'getMessage']);
Route::get('/get-messages-admin', [ChatController::class, 'getMessageAdmin']);
Route::post('/send-message', [ChatController::class, 'sendMessage']);
Route::post('/send-message-admin', [ChatController::class, 'sendMessageAdmin']);
Route::get('/unread-messages', [ChatController::class, 'unreadMessages']);
