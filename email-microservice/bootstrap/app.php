<?php

// Fix for Windows SOCKET_EAGAIN constant issue with php-amqplib
if (!defined('SOCKET_EAGAIN')) {
    define('SOCKET_EAGAIN', 11);
}
if (!defined('SOCKET_EWOULDBLOCK')) {
    define('SOCKET_EWOULDBLOCK', 11);
}
if (!defined('SOCKET_EINTR')) {
    define('SOCKET_EINTR', 4);
}

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
