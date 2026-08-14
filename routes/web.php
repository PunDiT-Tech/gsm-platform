<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CheckOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MyOrdersController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SupportTicketController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/services', [HomeController::class, 'services'])->name('services.index');
Route::get('/services/{slug}', [ServiceController::class, 'show'])->name('services.show');
Route::view('/how-it-works', 'pages.how-it-works')->name('how-it-works');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/announcements', [HomeController::class, 'announcements'])->name('announcements');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->middleware('throttle:contact')->name('contact.submit');
Route::get('/check-order', [CheckOrderController::class, 'create'])->name('order.lookup');
Route::post('/check-order', [CheckOrderController::class, 'lookup'])->middleware('throttle:order-lookup')->name('order.lookup.submit');
Route::post('/orders', [OrderController::class, 'store'])->middleware('throttle:orders')->name('orders.store');
Route::get('/orders/{order}/{token}', [OrderController::class, 'confirmation'])->name('orders.confirmation');
Route::get('/orders/{order}/{token}/pay', [PaymentController::class, 'show'])->name('orders.payment');
Route::post('/orders/{order}/payment', [PaymentController::class, 'upload'])->middleware('throttle:uploads')->name('orders.payment.upload');

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
require __DIR__ . '/admin.php';

Route::get('/{slug}', [HomeController::class, 'page'])->whereIn('slug', ['terms', 'privacy', 'refunds', 'acceptable-use'])->name('page');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store'])->middleware('throttle:auth');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:auth');

    Route::get('two-factor-challenge', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'create'])->name('two-factor.challenge');
    Route::post('two-factor-challenge', [\App\Http\Controllers\Auth\TwoFactorChallengeController::class, 'store'])->middleware('throttle:two-factor')->name('two-factor.challenge.store');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('throttle:password')->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('throttle:password')->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('dashboard/orders', [MyOrdersController::class, 'index'])->name('orders.index');
    Route::get('dashboard/orders/{order}', [MyOrdersController::class, 'show'])->name('orders.show');
    Route::post('dashboard/orders/{order}/message', [MyOrdersController::class, 'message'])->name('orders.message');
    Route::post('dashboard/orders/{order}/upload', [MyOrdersController::class, 'upload'])->name('orders.upload');
    Route::get('dashboard/orders/{order}/pay', [PaymentController::class, 'show'])->name('orders.pay');
    Route::get('dashboard/orders/result/{result}/download', [MyOrdersController::class, 'downloadResult'])->name('orders.result-download');
    Route::get('dashboard/orders/message/{message}/download', [MyOrdersController::class, 'downloadMessage'])->name('orders.message-download');

    Route::get('dashboard/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('dashboard/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('dashboard/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    Route::get('dashboard/support', [SupportTicketController::class, 'index'])->name('support.index');
    Route::post('dashboard/support', [SupportTicketController::class, 'store'])->middleware('throttle:support')->name('support.store');
    Route::get('dashboard/support/{ticket}', [SupportTicketController::class, 'show'])->name('support.show');
    Route::post('dashboard/support/{ticket}/reply', [SupportTicketController::class, 'reply'])->middleware('throttle:support')->name('support.reply');

    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::patch('profile/password', [ProfileController::class, 'password'])->name('profile.password');

    Route::get('profile/two-factor', [\App\Http\Controllers\TwoFactorController::class, 'show'])->name('profile.two-factor');
    Route::post('profile/two-factor/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('profile.two-factor.enable');
    Route::post('profile/two-factor/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('profile.two-factor.confirm');
    Route::post('profile/two-factor/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('profile.two-factor.disable');
});
