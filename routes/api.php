// RabbitMQ routes
Route::prefix('rabbitmq')->group(function () {
    Route::post('/send-email', [EmailController::class, 'sendEmailViaRabbitMQ']);
    Route::get('/queue-status', [EmailController::class, 'getRabbitMQQueueStatus']);
    Route::get('/queue-stats', [EmailController::class, 'getRabbitMQQueueStats']);
    Route::post('/process-queue', [EmailController::class, 'processRabbitMQQueue']);
});

// Test route that doesn't use RabbitMQ
Route::get('/test-status', [EmailController::class, 'testSystemStatus']);
