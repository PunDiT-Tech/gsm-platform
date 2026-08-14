<?php

use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\HomepageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ServiceContentController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\TelegramController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware(['auth', 'verified', 'role:SUPER_ADMIN,ADMIN,SUPPORT,FINANCE'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Categories
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:services.view')->name('admin.categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->middleware('permission:services.create')->name('admin.categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:services.create')->name('admin.categories.store');
    Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->middleware('permission:services.edit')->name('admin.categories.edit');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:services.edit')->name('admin.categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:services.delete')->name('admin.categories.destroy');
    Route::post('/categories/{category}/toggle', [CategoryController::class, 'toggle'])->middleware('permission:services.edit')->name('admin.categories.toggle');

    // Services
    Route::get('/services', [ServiceController::class, 'index'])->middleware('permission:services.view')->name('admin.services.index');
    Route::get('/services/create', [ServiceController::class, 'create'])->middleware('permission:services.create')->name('admin.services.create');
    Route::post('/services', [ServiceController::class, 'store'])->middleware('permission:services.create')->name('admin.services.store');
    Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->middleware('permission:services.edit')->name('admin.services.edit');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->middleware('permission:services.edit')->name('admin.services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->middleware('permission:services.delete')->name('admin.services.destroy');
    Route::post('/services/{service}/toggle', [ServiceController::class, 'toggle'])->middleware('permission:services.edit')->name('admin.services.toggle');
    Route::post('/services/{service}/feature', [ServiceController::class, 'feature'])->middleware('permission:services.edit')->name('admin.services.feature');

    // Service content
    Route::post('/services/{service}/fields', [ServiceContentController::class, 'storeField'])->middleware('permission:services.edit')->name('admin.services.fields.store');
    Route::delete('/services/{service}/fields/{field}', [ServiceContentController::class, 'destroyField'])->middleware('permission:services.edit')->name('admin.services.fields.destroy');
    Route::post('/services/{service}/blocks', [ServiceContentController::class, 'storeBlock'])->middleware('permission:services.edit')->name('admin.services.blocks.store');
    Route::delete('/services/{service}/blocks/{block}', [ServiceContentController::class, 'destroyBlock'])->middleware('permission:services.edit')->name('admin.services.blocks.destroy');
    Route::post('/services/{service}/links', [ServiceContentController::class, 'storeLink'])->middleware('permission:services.edit')->name('admin.services.links.store');
    Route::delete('/services/{service}/links/{link}', [ServiceContentController::class, 'destroyLink'])->middleware('permission:services.edit')->name('admin.services.links.destroy');

    // Orders
    Route::get('/orders', [OrderController::class, 'index'])->middleware('permission:orders.view')->name('admin.orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->middleware('permission:orders.view')->name('admin.orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'status'])->middleware('permission:orders.status')->name('admin.orders.status');
    Route::post('/orders/{order}/message', [OrderController::class, 'message'])->middleware('permission:orders.message')->name('admin.orders.message');
    Route::post('/orders/{order}/result', [OrderController::class, 'result'])->middleware('permission:orders.result')->name('admin.orders.result');
    Route::get('/orders/result/{result}/download', [OrderController::class, 'downloadResult'])->middleware('permission:orders.view')->name('admin.orders.result-download');
    Route::get('/orders/field/{fieldValue}/download', [OrderController::class, 'downloadField'])->middleware('permission:orders.view')->name('admin.orders.field-download');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->middleware('permission:payments.view')->name('admin.payments.index');
    Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->middleware('permission:payments.verify')->name('admin.payments.verify');
    Route::post('/payments/{payment}/reject', [PaymentController::class, 'reject'])->middleware('permission:payments.reject')->name('admin.payments.reject');
    Route::get('/payments/proof/{proof}/download', [PaymentController::class, 'downloadProof'])->middleware('permission:payments.view')->name('admin.payments.proof-download');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:customers.view')->name('admin.customers.index');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:customers.view')->name('admin.customers.show');

    // Support
    Route::get('/support', [SupportController::class, 'index'])->middleware('permission:support.view')->name('admin.support.index');
    Route::get('/support/{ticket}', [SupportController::class, 'show'])->middleware('permission:support.view')->name('admin.support.show');
    Route::post('/support/{ticket}/reply', [SupportController::class, 'reply'])->middleware('permission:support.manage')->name('admin.support.reply');
    Route::post('/support/{ticket}/status', [SupportController::class, 'status'])->middleware('permission:support.manage')->name('admin.support.status');

    // Homepage
    Route::get('/homepage', [HomepageController::class, 'index'])->middleware('permission:homepage.manage')->name('admin.homepage.index');
    Route::post('/homepage', [HomepageController::class, 'storeShowcase'])->middleware('permission:homepage.manage')->name('admin.homepage.store');
    Route::put('/homepage/{showcase}', [HomepageController::class, 'updateShowcase'])->middleware('permission:homepage.manage')->name('admin.homepage.update');
    Route::delete('/homepage/{showcase}', [HomepageController::class, 'destroyShowcase'])->middleware('permission:homepage.manage')->name('admin.homepage.destroy');

    // Announcements
    Route::get('/announcements', [AnnouncementController::class, 'index'])->middleware('permission:announcements.manage')->name('admin.announcements.index');
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->middleware('permission:announcements.manage')->name('admin.announcements.create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->middleware('permission:announcements.manage')->name('admin.announcements.store');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->middleware('permission:announcements.manage')->name('admin.announcements.edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->middleware('permission:announcements.manage')->name('admin.announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->middleware('permission:announcements.manage')->name('admin.announcements.destroy');

    // FAQ
    Route::get('/faq', [FaqController::class, 'index'])->middleware('permission:homepage.manage')->name('admin.faq.index');
    Route::post('/faq', [FaqController::class, 'store'])->middleware('permission:homepage.manage')->name('admin.faq.store');
    Route::put('/faq/{faq}', [FaqController::class, 'update'])->middleware('permission:homepage.manage')->name('admin.faq.update');
    Route::delete('/faq/{faq}', [FaqController::class, 'destroy'])->middleware('permission:homepage.manage')->name('admin.faq.destroy');

    // Telegram
    Route::get('/telegram', [TelegramController::class, 'index'])->middleware('permission:telegram.manage')->name('admin.telegram.index');
    Route::post('/telegram', [TelegramController::class, 'update'])->middleware('permission:telegram.manage')->name('admin.telegram.update');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->middleware('permission:reports.view')->name('admin.reports.index');

    // Staff
    Route::get('/staff', [StaffController::class, 'index'])->middleware('permission:admins.manage')->name('admin.staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->middleware('permission:admins.manage')->name('admin.staff.store');
    Route::put('/staff/{user}', [StaffController::class, 'update'])->middleware('permission:admins.manage')->name('admin.staff.update');
    Route::delete('/staff/{user}', [StaffController::class, 'destroy'])->middleware('permission:admins.manage')->name('admin.staff.destroy');

    // Settings
    Route::get('/settings', [SettingsController::class, 'index'])->middleware('permission:settings.manage')->name('admin.settings.index');
    Route::post('/settings/website', [SettingsController::class, 'updateWebsite'])->middleware('permission:settings.manage')->name('admin.settings.website');
    Route::post('/settings/methods/{method}', [SettingsController::class, 'updateMethod'])->middleware('permission:settings.manage')->name('admin.settings.methods.update');

    // Audit logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->middleware('permission:audit_logs.view')->name('admin.audit-logs.index');

    // Coupons
    Route::get('/coupons', [CouponController::class, 'index'])->middleware('permission:settings.manage')->name('admin.coupons.index');
    Route::post('/coupons', [CouponController::class, 'store'])->middleware('permission:settings.manage')->name('admin.coupons.store');
    Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->middleware('permission:settings.manage')->name('admin.coupons.update');
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->middleware('permission:settings.manage')->name('admin.coupons.destroy');

    // System health
    Route::get('/system', [SystemController::class, 'index'])->middleware('permission:settings.manage')->name('admin.system.index');
});