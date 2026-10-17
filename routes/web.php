<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ModerationController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Stream images directly from MySQL database (zero filesystem storage)
Route::get('/images/{path}', [ImageController::class, 'show'])->where('path', '.*')->name('images.show');
Route::get('/storage/{path}', [ImageController::class, 'show'])->where('path', '.*');

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/profile', [ProfileController::class, 'show'])
    ->middleware('auth')
    ->name('profile.show');

Route::get('/user/profile', [ProfileController::class, 'user'])
    ->middleware('auth')
    ->name('user.profile.show');

Route::post('/user/profile', [ProfileController::class, 'updateUserProfile'])
    ->middleware(['auth', 'role:user'])
    ->name('user.profile.update');

Route::post('/profile/password', [ProfileController::class, 'updatePassword'])
    ->middleware('auth')
    ->name('profile.password.update');

Route::get('/admin', [AdminController::class, 'index'])
    ->middleware(['auth', 'role:user'])
    ->name('admin.dashboard');

Route::post('/admin/profile', [AdminController::class, 'updateProfile'])
    ->middleware(['auth', 'role:user|admin'])
    ->name('admin.profile.update');

Route::get('/home', function () {
    return redirect()->route('posts.index');
})->middleware(['auth', 'role:user'])->name('post');


Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
});

Route::get('/posts/{post}', [PostController::class, 'show'])
    ->whereNumber('post')
    ->name('posts.show');

Route::post('/posts/{post}/like', [PostController::class, 'toggleLike'])
    ->middleware(['auth', 'role:user'])
    ->whereNumber('post')
    ->name('posts.like');

Route::get('/favorites', [FavoriteController::class, 'index'])
    ->middleware(['auth', 'role:user'])
    ->name('favorites.index');

Route::post('/purchase-requests', [PurchaseRequestController::class, 'store'])
    ->middleware(['auth', 'role:user'])
    ->name('purchase-requests.store');

Route::get('/user/purchase-requests', [PurchaseRequestController::class, 'userIndex'])
    ->middleware(['auth', 'role:user'])
    ->name('user.purchase-requests.index');

Route::middleware('auth')->group(function () {
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chats/{chat}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/chats/{chat}/messages', [ChatController::class, 'store'])->name('chats.messages.store');
});

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/admin/purchase-requests', [PurchaseRequestController::class, 'index'])
        ->name('admin.purchase-requests.index');
    Route::get('/admin/archive', [AdminController::class, 'archive'])
        ->name('admin.archive.index');
    Route::get('/admin/sold-animals', function () {
        return redirect()->route('admin.archive.index');
    })->name('admin.sold-animals.index');
    Route::post('/admin/posts/{post}/archive', [AdminController::class, 'archivePost'])
        ->name('admin.posts.archive');
    Route::post('/admin/purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])
        ->name('admin.purchase-requests.approve');
    Route::post('/admin/purchase-requests/{purchaseRequest}/mark-sold', [PurchaseRequestController::class, 'markSold'])
        ->name('admin.purchase-requests.mark-sold');
    Route::post('/admin/purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])
        ->name('admin.purchase-requests.reject');
});

// Main admin: reviews every listing before it is shown to the public
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/moderation', [ModerationController::class, 'index'])->name('moderation.index');
    Route::post('/moderation/posts/{post}/approve', [ModerationController::class, 'approve'])->name('moderation.approve');
    Route::post('/moderation/posts/{post}/reject', [ModerationController::class, 'reject'])->name('moderation.reject');

    // Rotating hero slides on the home page
    Route::resource('banners', BannerController::class)->except('show');
    Route::patch('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
});
