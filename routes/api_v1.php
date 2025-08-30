<?php

use App\Http\Controllers\Api\V1\TicketController;
use App\Http\Controllers\Api\V1\AuthorsController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\AuthorTicketsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function() {
    // Ticket routes
    Route::get('tickets/stats', [TicketController::class, 'stats'])->name('tickets.stats');
    Route::apiResource('tickets', TicketController::class)->except(['update']);
    Route::patch('tickets/{ticket}', [TicketController::class, 'update']);
    Route::post('tickets/{ticket}/classify', [TicketController::class, 'classify']);
    
    // Author routes
    Route::apiResource('authors', AuthorsController::class)->only(['index', 'show']);
    Route::apiResource('authors.tickets', AuthorTicketsController::class)->except(['update']);
    Route::patch('authors/{author}/tickets/{ticket}', [AuthorTicketsController::class, 'update']);
    
    // User routes
    Route::apiResource('users', UserController::class)->only(['index', 'show']);
});