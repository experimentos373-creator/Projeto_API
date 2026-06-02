<?php

use App\Http\Controllers\TshirtimageController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CustomerTshirtImageController;
use App\Http\Controllers\ServePrivateImageController;

Route::get('/', [TshirtimageController::class, 'shop'])->name('home');
Route::get('/tshirt-images/{tshirtImage}', [TshirtimageController::class, 'show'])->name('tshirt-images.show');

// Carrinho (disponível para todos, incluindo anónimos)
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add/{tshirtImage}', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update/{key}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{key}', [CartController::class, 'remove'])->name('cart.remove');
Route::delete('/cart/destroy', [CartController::class, 'destroy'])->name('cart.destroy');

// Checkout e Encomendas (implementado no G4)
Route::get('/checkout', [App\Http\Controllers\CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [App\Http\Controllers\CheckoutController::class, 'store'])->name('checkout.store');

// Consulta e Transições de Encomendas — apenas autenticados
Route::middleware(['auth'])->group(function () {
    Route::get('/orders', [App\Http\Controllers\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [App\Http\Controllers\OrderController::class, 'show'])->name('orders.show');
    Route::patch('/orders/{order}/status', [App\Http\Controllers\OrderController::class, 'updateStatus'])->name('orders.update-status');
    Route::get('/orders/{order}/receipt', [App\Http\Controllers\OrderController::class, 'downloadReceipt'])->name('orders.receipt');
});


// Autenticação via Fortify (com nomes esperados pelos testes)
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;
use Laravel\Fortify\Http\Controllers\RegisteredUserController;

Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
Route::post('register', [RegisteredUserController::class, 'store'])->name('register.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    // G5 — Imagens Personalizadas do Cliente (CRUD)
    Route::middleware(['customer'])->prefix('customer')->name('customer.')->group(function () {
        Route::resource('tshirt-images', CustomerTshirtImageController::class)
            ->parameters(['tshirt-images' => 'tshirt_image']);
    });

    // G5 — Servir ficheiros privados de imagens (protegido por auth + ownership)
    Route::get('/private/tshirt-images/{filename}', [ServePrivateImageController::class, 'show'])
        ->name('private.tshirt-images.show');

    // Perfil
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Rotas Administrativas
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        // Painel de Estatísticas
        Route::get('/statistics', [App\Http\Controllers\Admin\StatisticsController::class, 'index'])->name('statistics.index');

        // Gestão de Utilizadores
        Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
        Route::post('/users', [App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit', [App\Http\Controllers\Admin\UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/block', [App\Http\Controllers\Admin\UserController::class, 'toggleBlock'])->name('users.block');
        Route::delete('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

        // Gestão de Preços (Registo Único)
        Route::get('/prices/edit', [App\Http\Controllers\PriceController::class, 'edit'])->name('prices.edit');
        Route::put('/prices', [App\Http\Controllers\PriceController::class, 'update'])->name('prices.update');

        // Gestão de Categorias
        Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class)->except(['show']);

        // Gestão de Imagens de T-Shirt (Catálogo)
        Route::resource('tshirt-images', App\Http\Controllers\Admin\TshirtImageController::class)->except(['show']);

        // Gestão de Cores
        Route::resource('colors', App\Http\Controllers\Admin\ColorController::class)->except(['show']);
    });
});
