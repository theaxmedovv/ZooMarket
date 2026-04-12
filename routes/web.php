<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('posts.index');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
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
    ->middleware(['auth', 'role:seller'])
    ->name('admin.dashboard');

Route::post('/admin/profile', [AdminController::class, 'updateProfile'])
    ->middleware(['auth', 'role:seller'])
    ->name('admin.profile.update');

Route::get('/home', function () {
    return redirect()->route('posts.index');
})->middleware(['auth', 'role:user'])->name('home');

Route::get('/posts', [PostController::class, 'index'])->name('posts.index');

Route::middleware(['auth', 'role:seller'])->group(function () {
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

Route::post('/purchase-requests', [PurchaseRequestController::class, 'store'])
    ->middleware(['auth', 'role:user'])
    ->name('purchase-requests.store');

Route::get('/user/purchase-requests', [PurchaseRequestController::class, 'userIndex'])
    ->middleware(['auth', 'role:user'])
    ->name('user.purchase-requests.index');

Route::middleware(['auth', 'role:seller'])->group(function () {
    Route::get('/admin/purchase-requests', [PurchaseRequestController::class, 'index'])
        ->name('admin.purchase-requests.index');
    Route::get('/admin/sold-animals', [PurchaseRequestController::class, 'soldAnimals'])
        ->name('admin.sold-animals.index');
    Route::post('/admin/purchase-requests/{purchaseRequest}/approve', [PurchaseRequestController::class, 'approve'])
        ->name('admin.purchase-requests.approve');
    Route::post('/admin/purchase-requests/{purchaseRequest}/reject', [PurchaseRequestController::class, 'reject'])
        ->name('admin.purchase-requests.reject');
});
