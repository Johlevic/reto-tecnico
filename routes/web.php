<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VoucherController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use App\Models\User;

// Página de bienvenida
// (Aquí podrías agregar la ruta de la página de bienvenida si aplica)

// Dashboard protegido por autenticación
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Incluir rutas de autenticación generadas por Breeze
require __DIR__ . '/auth.php';

// Grupo de rutas protegidas por autenticación
Route::middleware('auth')->group(function () {
    // Página principal del Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas relacionadas con Vouchers
    Route::get('/comprobantes-registrados', [VoucherController::class, 'getFilteredVouchers'])->name('vouchers');
    Route::get('/totales-por-moneda', [VoucherController::class, 'getTotalAmountsByCurrency'])->name('totals.by.currency');
    Route::get('/upload-errors/view', [VoucherController::class, 'showUploadErrors'])->name('upload.errors');
    Route::get('/vouchers-articulos', [VoucherController::class, 'VoucherArticulos'])->name('vouchers.articulos');
    Route::get('/voucher-articulos/search', [VoucherController::class, 'searchVoucherArticulos'])->name('voucher.search');
    Route::resource('voucher', VoucherController::class);
});

// Rutas para procesar el archivo XML y otras acciones relacionadas
Route::post('/comprobantes/regularizar', [VoucherController::class, 'regularizarComprobantes']);
Route::get('/comprobante/subir', [VoucherController::class, 'showUploadForm'])->name('voucher.upload.form');
Route::post('/comprobante/subir', [VoucherController::class, 'upload'])->name('voucher.upload');
