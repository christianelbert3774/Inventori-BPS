<?php

use Illuminate\Support\Facades\Route;
use App\Models\Barang;

Route::get('/barang', function () {
    return response()->json(Barang::all());
});