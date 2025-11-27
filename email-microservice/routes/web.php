<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes (require authentication)
Route::middleware(['auth'])->group(function () {
    Route::get('/', function () {
        return view('home');
    });

    // Email providers management page
    Route::get('/providers', function () {
        return view('providers');
    });

    // Email logs view page
    Route::get('/email-logs', function () {
        return view('email-logs');
    });

    // RabbitMQ test page
    Route::get('/rabbitmq-test', function () {
        return view('rabbitmq-test');
    });

    Route::get('/inbound-emails', function () {
        return view('inbound-emails');
    });

    // Outbox management page
    Route::get('/outbox', function () {
        return view('outbox');
    })->name('outbox');

    // Replied emails page
    Route::get('/replied-emails', function () {
        return view('replied-emails');
    })->name('replied-emails');

    // Email tracking dashboard
    Route::get('/email-tracking', function () {
        return view('email-tracking');
    })->name('email.tracking');

    // Email templates management page
    Route::get('/templates', function () {
        return view('templates');
    })->name('templates');
    
    // Also serve templates page at /api/email/templates when accessed via browser (not API)
    Route::get('/api/email/templates', function (Request $request) {
        // Check if request wants JSON (API call) - check Accept header explicitly
        $acceptHeader = $request->header('Accept', '');
        $isApiRequest = str_contains($acceptHeader, 'application/json') || 
                       $request->wantsJson() || 
                       $request->expectsJson() ||
                       $request->ajax();
        
        if ($isApiRequest) {
            // Delegate to API controller for JSON response
            return app(\App\Http\Controllers\Api\EmailController::class)->getTemplates($request);
        }
        // Otherwise, serve the HTML page (browser navigation)
        return view('templates');
    });

    // File download route for email attachments
    Route::get('/email/file/{path}', function (string $path) {
        $decodedPath = base64_decode($path);
        
        if (!Storage::disk('local')->exists($decodedPath)) {
            abort(404, 'File not found');
        }
        
        $file = Storage::disk('local')->get($decodedPath);
        $mimeType = Storage::disk('local')->mimeType($decodedPath);
        $filename = basename($decodedPath);
        
        return response($file, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    })->name('email.file.download');
});
