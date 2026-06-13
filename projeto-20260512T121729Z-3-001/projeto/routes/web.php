<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ColorController;
use App\Http\Controllers\Admin\StatisticsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TshirtimageController;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

Route::get('/', [TshirtimageController::class, 'shop'])->name('home');
Route::get('/tshirt-images/{tshirtImage}', [TshirtimageController::class, 'show'])->name('tshirt-images.show');

// Carrinho (disponível para todos, incluindo anónimos)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{tshirtImage}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/destroy', [CartController::class, 'destroy'])->name('cart.destroy');

// Checkout e Encomendas (implementado no G4)
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

// Consulta e Transições de Encomendas — apenas autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/receipt', [OrderController::class, 'downloadReceipt'])->name('orders.receipt');
});

// Autenticação via Fortify (com nomes esperados pelos testes)

Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // Perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Rotas Administrativas
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Painel de Estatísticas
        Route::get('/statistics', [StatisticsController::class, 'index'])->name('statistics.index');

        // Gestão de Utilizadores
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/block', [UserController::class, 'toggleBlock'])->name('users.block');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        // Gestão de Preços (Registo Único)
        Route::get('/prices/edit', [PriceController::class, 'edit'])->name('prices.edit');
        Route::put('/prices', [PriceController::class, 'update'])->name('prices.update');

        // Gestão de Categorias
        Route::resource('categories', CategoryController::class)->except(['show']);

        // Gestão de Imagens de T-Shirt (Catálogo)
        Route::resource('tshirt-images', App\Http\Controllers\Admin\TshirtImageController::class)->except(['show']);

        // Gestão de Cores
        Route::resource('colors', ColorController::class)->except(['show']);
    });
});
