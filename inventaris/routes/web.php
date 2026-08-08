<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\RuanganController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

use App\Models\Barang;
use App\Models\Ruangan;

Route::get('/dashboard', function () {
    $totalBarang = Barang::sum('jumlah');
    $totalRuangan = Ruangan::count();
    $kondisiBaik = Barang::where('kondisi', 'Baik')->sum('jumlah');
    $kondisiRusak = Barang::whereIn('kondisi', ['Rusak', 'Mati'])->sum('jumlah');

    return view('dashboard', compact('totalBarang', 'totalRuangan', 'kondisiBaik', 'kondisiRusak'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('barang', BarangController::class);
    Route::resource('ruangan', RuanganController::class);
});

require __DIR__.'/auth.php';
