<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/migrate', function () {
    try {
        Artisan::call('migrate');
        return response()->json([
            'success' => true,
            'message' => 'Migrated successfully'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
});

Route::get('db', function () {
    $result = \Illuminate\Support\Facades\Process::run('sudo find / -name database.sqlite');
    return response()->json([
//        'data' => DB::connection()->getPdo()
        'success' => $result,
    ]);
});
