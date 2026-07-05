<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BotController;
use App\Http\Controllers\InventoryController;

// Dashboard Routes
Route::get('/', [BotController::class, 'dashboard'])->name('bot.dashboard');
Route::get('/inventory', [InventoryController::class, 'dashboard'])->name('inventory.dashboard');

// API Routes
Route::prefix('api')->group(function () {
    Route::post('/poll', [BotController::class, 'poll']);
    Route::get('/users', [BotController::class, 'getUsers']);
    Route::post('/respawn', [BotController::class, 'triggerRespawn']);
    Route::post('/track', [InventoryController::class, 'trackItem']);
    Route::post('/update', [InventoryController::class, 'updateItem']);
    Route::post('/reset', [InventoryController::class, 'resetCounts']);
});