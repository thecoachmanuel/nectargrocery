<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\Admin\BusinessSetupController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryAttributeController;
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\CustomerNotificationController;
use App\Http\Controllers\Admin\DeliveryChargeController;
use App\Http\Controllers\Admin\EmployeeManageController;
use App\Http\Controllers\Admin\FirebaseController;
use App\Http\Controllers\Admin\FlashSaleController;
use App\Http\Controllers\Admin\FooterController;
use App\Http\Controllers\Admin\GeneraleSettingController;
use App\Http\Controllers\Admin\GoogleReCaptchaController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MailConfigurationController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PusherConfigController;
use App\Http\Controllers\Admin\ReviewsController;
use App\Http\Controllers\Admin\RiderController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\ShopController;
use App\Http\Controllers\Admin\SMSGatewaySetupController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\ThemeColorController;
use App\Http\Controllers\Admin\TicketIssueTypeController;
use App\Http\Controllers\Admin\VatTaxController;
use App\Http\Controllers\Admin\VerifyManageController;
use App\Http\Controllers\Admin\WhatsAppChatController;
use App\Http\Controllers\Admin\WithdrawController;
use App\Http\Controllers\Admin\MarketController;
use App\Http\Controllers\Admin\AdController;
use App\Http\Controllers\Admin\CheckOnlineUserController;
use App\Http\Controllers\Admin\SocialAuthController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Gateway\PaymentGatewayController as GatewayPaymentController;

use App\Http\Controllers\Shop\Auth\LoginController as ShopLoginController;
use App\Http\Controllers\Shop\DashboardController as ShopDashboardController;
use App\Http\Controllers\Shop\BannerController as ShopBannerController;
use App\Http\Controllers\Shop\BrandController as ShopBrandController;
use App\Http\Controllers\Shop\BulkProductExportController as ShopBulkProductExportController;
use App\Http\Controllers\Shop\BulkProductImportController as ShopBulkProductImportController;
use App\Http\Controllers\Shop\CustomerMessageController as ShopCustomerMessageController;
use App\Http\Controllers\Shop\EmployeeController as ShopEmployeeController;
use App\Http\Controllers\Shop\FlashSaleController as ShopFlashSaleController;
use App\Http\Controllers\Shop\GalleryController as ShopGalleryController;
use App\Http\Controllers\Shop\NotificationController as ShopNotificationController;
use App\Http\Controllers\Shop\OrderController as ShopOrderController;
use App\Http\Controllers\Shop\POSController as ShopPOSController;
use App\Http\Controllers\Shop\ProductController as ShopProductController;
use App\Http\Controllers\Shop\ProfileController as ShopProfileController;
use App\Http\Controllers\Shop\SubscriptionController as ShopSubscriptionController;
use App\Http\Controllers\Shop\VoucherController as ShopVoucherController;
use App\Http\Controllers\Shop\WithdrawController as ShopWithdrawController;

// ─── Customer Frontend Website SPA Routes ──────────────────────────────────────
Route::get('/', function () {
    return view('app');
})->name('home');

Route::get('/seed-demo-data-now', function () {
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    return 'Database seeded with all products, categories, shops, and banners successfully!';
});

Route::get('/{any}', function () {
    return view('app');
})->where('any', '^(?!admin|shop|install|api|storage|filemanager).*');

// ─── Admin Auth Routes ─────────────────────────────────────────────────────────
Route::prefix('admin')->name('admin.')->group(function () {

    // Guest-only routes
    Route::middleware('guest:web')->group(function () {
        Route::controller(AdminLoginController::class)->group(function () {
            Route::get('/login', 'index')->name('login');
            Route::post('/login', 'login')->name('login.submit');
        });
    });

    // Authenticated admin routes
    Route::middleware(['auth:web', 'role:root|admin|employee'])->group(function () {

        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

        // Dashboard
        Route::controller(DashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard.index');
            Route::get('/dashboard/statistics', 'orderStatistics')->name('dashboard.statistics');
            Route::get('/dashboard/notification', 'notifications')->name('dashboard.notification');
        });

        // Profile
        Route::controller(ProfileController::class)->group(function () {
            Route::get('/profile', 'index')->name('profile.index');
            Route::get('/profile/edit', 'edit')->name('profile.edit');
            Route::get('/profile/change-password', 'changePassword')->name('profile.change-password');
            Route::post('/profile/update', 'update')->name('profile.update');
            Route::post('/profile/password', 'updatePassword')->name('profile.password');
        });

        // Banners
        Route::resource('banner', BannerController::class)->names('banner');

        // Categories
        Route::resource('category', CategoryController::class)->names('category');
        Route::resource('category-attribute', CategoryAttributeController::class)->names('categoryAttribute');

        // Brands
        Route::resource('brand', BrandController::class)->names('brand');

        // Products
        Route::controller(ProductController::class)->group(function () {
            Route::get('/product', 'index')->name('product.index');
            Route::get('/product/{id}', 'show')->name('product.show');
            Route::post('/product/{id}/status', 'status')->name('product.status');
            Route::delete('/product/{id}', 'destroy')->name('product.destroy');
        });

        // Orders
        Route::controller(OrderController::class)->group(function () {
            Route::get('/order/{status?}', 'index')->name('order.index');
            Route::get('/order/show/{id}', 'show')->name('order.show');
            Route::post('/order/{id}/status', 'updateStatus')->name('order.status');
        });

        // Shops
        Route::controller(ShopController::class)->group(function () {
            Route::get('/shop', 'index')->name('shop.index');
            Route::get('/shop/create', 'create')->name('shop.create');
            Route::post('/shop/store', 'store')->name('shop.store');
            Route::get('/shop/{shop}/edit', 'edit')->name('shop.edit');
            Route::post('/shop/{shop}/update', 'update')->name('shop.update');
            Route::get('/shop/{shop}', 'show')->name('shop.show');
            Route::post('/shop/{id}/status', 'status')->name('shop.status');
            Route::delete('/shop/{id}', 'destroy')->name('shop.destroy');
        });

        // Customers
        Route::controller(CustomerController::class)->group(function () {
            Route::get('/customer', 'index')->name('customer.index');
            Route::get('/customer/create', 'create')->name('customer.create');
            Route::post('/customer/store', 'store')->name('customer.store');
            Route::get('/customer/{user}/edit', 'edit')->name('customer.edit');
            Route::post('/customer/{user}/update', 'update')->name('customer.update');
            Route::get('/customer/{id}', 'show')->name('customer.show');
            Route::post('/customer/{id}/status', 'status')->name('customer.status');
            Route::delete('/customer/{id}', 'destroy')->name('customer.destroy');
        });

        // Riders
        Route::controller(RiderController::class)->group(function () {
            Route::get('/rider', 'index')->name('rider.index');
            Route::get('/rider/create', 'create')->name('rider.create');
            Route::post('/rider/store', 'store')->name('rider.store');
            Route::get('/rider/{rider}/edit', 'edit')->name('rider.edit');
            Route::post('/rider/{rider}/update', 'update')->name('rider.update');
            Route::get('/rider/{id}', 'show')->name('rider.show');
            Route::post('/rider/{id}/status', 'status')->name('rider.status');
        });

        // Flash Sales
        Route::resource('flash-sale', FlashSaleController::class)->names('flashSale');

        // Coupons
        Route::resource('coupon', CouponController::class)->names('coupon');

        // Delivery Charges
        Route::resource('delivery-charge', DeliveryChargeController::class)->names('deliveryCharge');

        // Countries
        Route::resource('country', CountryController::class)->names('country');

        // Currencies
        Route::resource('currency', CurrencyController::class)->names('currency');

        // Withdrawals
        Route::controller(WithdrawController::class)->group(function () {
            Route::get('/withdraw', 'index')->name('withdraw.index');
            Route::post('/withdraw/{id}/approve', 'approve')->name('withdraw.approve');
            Route::post('/withdraw/{id}/reject', 'reject')->name('withdraw.reject');
        });

        // Reviews
        Route::controller(ReviewsController::class)->group(function () {
            Route::get('/reviews', 'index')->name('reviews.index');
            Route::delete('/reviews/{id}', 'destroy')->name('reviews.destroy');
        });

        // Blogs / Pages
        Route::resource('blog', BlogController::class)->names('blog');
        Route::resource('page', PageController::class)->names('page');

        // Notifications
        Route::controller(NotificationController::class)->group(function () {
            Route::get('/notification', 'index')->name('notification.index');
            Route::get('/notification/show', 'show')->name('notification.show');
            Route::get('/notification/read-all', 'markAllAsRead')->name('notification.readAll');
            Route::get('/notification/{notification}/read', 'markAsRead')->name('notification.read');
            Route::delete('/notification/{notification}', 'destroy')->name('notification.destroy');
        });

        // Support / Tickets
        Route::controller(SupportController::class)->group(function () {
            Route::get('/support', 'index')->name('support.index');
            Route::delete('/support/{id}', 'delete')->name('support.delete');
        });
        Route::resource('support-ticket', SupportTicketController::class)->names('supportTicket');
        Route::resource('ticket-issue-type', TicketIssueTypeController::class)->names('ticketIssueType');

        // Subscription Plans
        Route::resource('subscription-plan', SubscriptionPlanController::class)->names('subscription-plan');

        // Settings
        Route::controller(GeneraleSettingController::class)->group(function () {
            Route::get('/generale-setting', 'index')->name('generale-setting.index');
            Route::post('/generale-setting/update', 'update')->name('generale-setting.update');
            Route::post('/generale-setting/update-command', 'runCommand')->name('generale-setting.update.command');
        });

        Route::controller(BusinessSetupController::class)->group(function () {
            Route::get('/business-setup', 'index')->name('business-setup.index');
            Route::post('/business-setup/update', 'update')->name('business-setup.update');
        });

        Route::controller(ThemeColorController::class)->group(function () {
            Route::get('/theme-color', 'index')->name('themeColor.index');
            Route::post('/theme-color/update', 'update')->name('themeColor.update');
            Route::post('/theme-color/change', 'change')->name('themeColor.change');
            Route::get('/theme-color/{id}/status', 'status')->name('themeColor.status');
            Route::get('/theme-color/{id}/offer-banner', 'offerBanner')->name('offerBanner.index');
        });

        Route::controller(LanguageController::class)->group(function () {
            Route::get('/language', 'index')->name('language.index');
            Route::post('/language/store', 'store')->name('language.store');
            Route::get('/language/{id}/edit', 'edit')->name('language.edit');
            Route::post('/language/update', 'update')->name('language.update');
            Route::delete('/language/{id}', 'destroy')->name('language.destroy');
        });

        Route::controller(MenuController::class)->group(function () {
            Route::get('/menu', 'index')->name('menu.index');
            Route::post('/menu/update', 'update')->name('menu.update');
        });

        Route::controller(FooterController::class)->group(function () {
            Route::get('/footer', 'index')->name('footer.index');
            Route::post('/footer/update', 'update')->name('footer.update');
        });

        Route::controller(SocialLinkController::class)->group(function () {
            Route::get('/social-link', 'index')->name('socialLink.index');
            Route::post('/social-link/update', 'update')->name('socialLink.update');
        });

        Route::controller(GoogleReCaptchaController::class)->group(function () {
            Route::get('/google-recaptcha', 'index')->name('googleReCaptcha.index');
            Route::post('/google-recaptcha/update', 'update')->name('googleReCaptcha.update');
        });

        Route::controller(MailConfigurationController::class)->group(function () {
            Route::get('/mail-config', 'index')->name('mailConfig.index');
            Route::post('/mail-config/update', 'update')->name('mailConfig.update');
            Route::post('/mail-config/send-test', 'sendTestMail')->name('mailConfig.sendTestMail');
        });

        Route::controller(FirebaseController::class)->group(function () {
            Route::get('/firebase', 'index')->name('firebase.index');
            Route::post('/firebase/update', 'update')->name('firebase.update');
        });

        Route::controller(PusherConfigController::class)->group(function () {
            Route::get('/pusher', 'index')->name('pusher.index');
            Route::post('/pusher/update', 'update')->name('pusher.update');
        });

        Route::controller(SMSGatewaySetupController::class)->group(function () {
            Route::get('/sms-gateway', 'index')->name('smsGateway.index');
            Route::post('/sms-gateway/update', 'update')->name('smsGateway.update');
        });

        Route::controller(WhatsAppChatController::class)->group(function () {
            Route::get('/whatsapp', 'index')->name('whatsapp.index');
            Route::post('/whatsapp/update', 'update')->name('whatsapp.update');
        });

        Route::controller(PaymentGatewayController::class)->group(function () {
            Route::get('/payment-gateway', 'index')->name('paymentGateway.index');
            Route::post('/payment-gateway/update', 'update')->name('paymentGateway.update');
            Route::post('/payment-gateway/{id}/status', 'status')->name('paymentGateway.status');
        });

        Route::controller(VatTaxController::class)->group(function () {
            Route::get('/vat-tax', 'index')->name('vatTax.index');
            Route::post('/vat-tax/update', 'update')->name('vatTax.update');
        });

        Route::controller(RolePermissionController::class)->group(function () {
            Route::get('/role', 'index')->name('role.index');
            Route::post('/role/store', 'store')->name('role.store');
            Route::post('/role/update', 'update')->name('role.update');
            Route::delete('/role/{id}', 'destroy')->name('role.destroy');
        });

        Route::controller(EmployeeManageController::class)->group(function () {
            Route::get('/employee', 'index')->name('employee.index');
            Route::get('/employee/create', 'create')->name('employee.create');
            Route::post('/employee/store', 'store')->name('employee.store');
            Route::get('/employee/{id}/edit', 'edit')->name('employee.edit');
            Route::post('/employee/update', 'update')->name('employee.update');
            Route::delete('/employee/{id}', 'destroy')->name('employee.destroy');
        });

        Route::controller(ContactUsController::class)->group(function () {
            Route::get('/contact-us', 'index')->name('contactUs.index');
            Route::post('/contact-us/{id}/update', 'update')->name('contactUs.update');
        });

        Route::controller(CustomerNotificationController::class)->group(function () {
            Route::get('/customer-notification', 'index')->name('customerNotification.index');
            Route::post('/customer-notification/send', 'send')->name('customerNotification.send');
        });

        Route::controller(VerifyManageController::class)->group(function () {
            Route::get('/verify-manage', 'index')->name('verifyManage.index');
            Route::post('/verify-manage/{id}/status', 'status')->name('verifyManage.status');
        });

        Route::controller(AdController::class)->group(function () {
            Route::get('/ad', 'index')->name('ad.index');
            Route::get('/ad/create', 'create')->name('ad.create');
            Route::post('/ad/store', 'store')->name('ad.store');
            Route::get('/ad/{ad}/edit', 'edit')->name('ad.edit');
            Route::post('/ad/{id}/update', 'update')->name('ad.update');
            Route::delete('/ad/{id}', 'destroy')->name('ad.destroy');
        });

        Route::controller(CheckOnlineUserController::class)->group(function () {
            Route::get('/online-users', 'index')->name('onlineUsers.index');
        });

        // Route Aliases & Stubs for Admin
        Route::get('/business-setting', [BusinessSetupController::class, 'index'])->name('business-setting.index');
        Route::get('/sms-gateway-alias', [SMSGatewaySetupController::class, 'index'])->name('sms-gateway.index');
        Route::get('/verification-alias', [VerifyManageController::class, 'index'])->name('verification.index');
        Route::get('/social-auth', [SocialAuthController::class, 'index'])->name('socialAuth.index');
        Route::post('/social-auth/update', [SocialAuthController::class, 'update'])->name('socialAuth.update');
        Route::get('/whatsapp-chat-alias', [WhatsAppChatController::class, 'index'])->name('whatsAppChat.index');
        Route::get('/ai-prompt', [GeneraleSettingController::class, 'index'])->name('aiPrompt.index');
        Route::get('/ai-prompt/configure', [GeneraleSettingController::class, 'index'])->name('aiPrompt.configure');
    });
});

Route::middleware(['auth:web'])->prefix('admin')->group(function () {
    Route::controller(MarketController::class)->group(function () {
        Route::get('/market', 'index')->name('market.index');
        Route::get('/marketplace', 'index')->name('marketplace.index');
        Route::get('/marketplace/upgrade', 'upgrade')->name('marketplace.upgrade');
        Route::get('/marketplace/addons', 'addons')->name('marketplace.addons');
    });
});

// ─── Shop Routes ─────────────────────────────────────────────────────────────
Route::prefix('shop')->name('shop.')->group(function () {
    Route::middleware('guest:web')->group(function () {
        Route::controller(ShopLoginController::class)->group(function () {
            Route::get('/login', 'index')->name('login');
            Route::post('/login', 'login')->name('login.submit');
            Route::get('/register', 'create')->name('register');
            Route::post('/register', 'store')->name('register.submit');
        });
    });

    Route::middleware(['auth:web'])->group(function () {
        Route::post('/logout', [ShopLoginController::class, 'logout'])->name('logout');

        Route::controller(ShopDashboardController::class)->group(function () {
            Route::get('/dashboard', 'index')->name('dashboard.index');
            Route::get('/dashboard/statistics', 'orderStatistics')->name('dashboard.statistics');
            Route::get('/dashboard/notification', 'notifications')->name('dashboard.notification');
        });

        Route::controller(ShopProfileController::class)->group(function () {
            Route::get('/profile', 'index')->name('profile.index');
            Route::get('/profile/edit', 'edit')->name('profile.edit');
            Route::get('/profile/change-password', 'changePassword')->name('profile.change-password');
            Route::post('/profile/update', 'update')->name('profile.update');
            Route::post('/profile/password', 'updatePassword')->name('profile.password');
            Route::post('/profile/update-password', 'updatePassword')->name('profile.update-password');
        });

        Route::controller(ShopNotificationController::class)->group(function () {
            Route::get('/notification', 'index')->name('notification.show');
            Route::get('/notification/read-all', 'readAll')->name('notification.readAll');
            Route::get('/notification/{id}/read', 'read')->name('notification.read');
        });

        Route::controller(ShopSubscriptionController::class)->group(function () {
            Route::get('/subscription', 'index')->name('subscription.index');
        });

        Route::controller(ShopOrderController::class)->group(function () {
            Route::get('/order/download-invoice/{id}', 'downloadInvoice')->name('download-invoice');
            Route::get('/order/payment-slip/{id}', 'paymentSlip')->name('payment-slip');
            Route::post('/order/attach-barcode', 'attachBarcode')->name('order.attach.barcode');
            Route::post('/order/fetch-products', 'fetchProducts')->name('order.fetch.products');
        });

        Route::controller(ShopProductController::class)->group(function () {
            Route::get('/product/{id}/toggle', 'toggle')->name('product.toggle');
        });

        Route::resource('flash-sale', ShopFlashSaleController::class)->names('flashSale');
        Route::resource('brand', ShopBrandController::class)->names('brand');
        Route::resource('banner', ShopBannerController::class)->names('banner');
        Route::resource('product', ShopProductController::class)->names('product');
        Route::resource('order', ShopOrderController::class)->names('order');
        Route::resource('withdraw', ShopWithdrawController::class)->names('withdraw');
        Route::resource('voucher', ShopVoucherController::class)->names('voucher');

        Route::controller(ShopPOSController::class)->group(function () {
            Route::get('/pos', 'index')->name('pos.index');
            Route::get('/pos/sales', 'sales')->name('pos.sales');
            Route::get('/pos/draft', 'draft')->name('pos.draft');
            Route::delete('/pos/draft/{posCart}', 'draftDelete')->name('pos.draft.delete');
            Route::get('/pos/invoice/{id}', 'invoice')->name('pos.invoice');
            Route::match(['get', 'post'], '/pos/product', 'getProduct')->name('pos.product');
            Route::match(['get', 'post'], '/pos/products', 'getProduct')->name('pos.products');
            Route::match(['get', 'post'], '/pos/get-cart', 'getCart')->name('pos.getCart');
            Route::match(['get', 'post'], '/pos/cart', 'getCart')->name('pos.cart');
            Route::post('/pos/add-to-cart', 'addToCart')->name('pos.addToCart');
            Route::post('/pos/add-to-cart-alias', 'addToCart')->name('pos.add-to-cart');
            Route::post('/pos/update-cart', 'updateCart')->name('pos.updateCart');
            Route::post('/pos/update-cart-alias', 'updateCart')->name('pos.update-cart');
            Route::post('/pos/remove-cart', 'removeCart')->name('pos.removeCart');
            Route::post('/pos/remove-cart-alias', 'removeCart')->name('pos.remove-cart');
            Route::match(['get', 'post'], '/pos/product-detail', 'getProductDetail')->name('pos.product.detail');
            Route::match(['get', 'post'], '/pos/product-detail-alias', 'getProductDetail')->name('pos.product-detail');
            Route::post('/pos/apply-coupon', 'applyCoupon')->name('pos.applyCoupon');
            Route::post('/pos/apply-coupon-alias', 'applyCoupon')->name('pos.apply-coupon');
            Route::post('/pos/remove-coupon', 'removeCoupon')->name('pos.removeCoupon');
            Route::post('/pos/remove-coupon-alias', 'removeCoupon')->name('pos.remove-coupon');
            Route::post('/pos/submit-order', 'storeOrder')->name('pos.submitOrder');
            Route::post('/pos/store-order', 'storeOrder')->name('pos.order.store');
            Route::post('/pos/customer-store', 'storeCustomer')->name('pos.customerStore');
            Route::post('/pos/store-customer', 'storeCustomer')->name('pos.customer.store');
        });

        Route::get('/product-trashed', [ShopProductController::class, 'onlyTrashedProduct'])->name('product.trashedList');
        Route::resource('employee', ShopEmployeeController::class)->names('employee');
        Route::get('/gallery', [ShopGalleryController::class, 'index'])->name('gallery.index');
        Route::get('/chat', [ShopCustomerMessageController::class, 'index'])->name('customer.chat.index');
        Route::get('/bulk-export', [ShopBulkProductExportController::class, 'index'])->name('bulk-product-export.index');
        Route::get('/bulk-import', [ShopBulkProductImportController::class, 'index'])->name('bulk-product-import.index');
    });
});

// ─── Filemanager ───────────────────────────────────────────────────────────────
Route::group(['prefix' => 'filemanager', 'middleware' => ['auth:web']], function () {
    \UniSharp\LaravelFilemanager\Lfm::routes();
});

// ─── Payment Gateway Routes ───────────────────────────────────────────────────
Route::controller(GatewayPaymentController::class)->group(function () {
    Route::get('/payment/{payment}/process', 'payment')->name('order.payment');
    Route::get('/payment/{payment}/success', 'success')->name('order.payment.success');
    Route::get('/payment/{payment}/cancel', 'cancel')->name('order.payment.cancel');
    Route::get('/payment/{payment}/success-view', 'paymentSuccess')->name('order.payment.success.view');
    Route::get('/payment/{payment}/cancel-view', 'paymentCancel')->name('order.payment.cancel.view');
});
