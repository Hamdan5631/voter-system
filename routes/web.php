<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'message' => 'Voters List API',
        'version' => '1.0.0',
    ]);
});
