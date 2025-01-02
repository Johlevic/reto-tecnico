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




// Rutas de autenticación generadas por Breeze
require __DIR__ . '/auth.php';


// Dashboard protegido por autenticación
Route::middleware('auth')->group(function () {
    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Página principal del Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    // Rutas de Voucher
    Route::get('/upload-errors/view', [VoucherController::class, 'showUploadErrors'])->name('upload.errors');
    Route::get('/comprobantes-registrados', [VoucherController::class, 'getFilteredVouchers'])->name('vouchers');
    Route::get('/totales-por-moneda', [VoucherController::class, 'getTotalAmountsByCurrency'])->name('totals.by.currency');
    Route::get('/vouchers-articulos', [VoucherController::class, 'VoucherArticulos'])->name('vouchers.articulos');
    Route::get('/voucher-articulos/search', [VoucherController::class, 'searchVoucherArticulos'])->name('voucher.search');
    Route::resource('voucher', VoucherController::class);
});

// Ruta para procesar el archivo XML y otras acciones de comprobantes
Route::post('/comprobantes/regularizar', [VoucherController::class, 'regularizarComprobantes']);
Route::get('/comprobante/subir', [VoucherController::class, 'showUploadForm'])->name('voucher.upload.form');
Route::post('/comprobante/subir', [VoucherController::class, 'upload'])->name('voucher.upload');
