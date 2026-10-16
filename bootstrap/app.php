<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias(['role' => \App\Http\Middleware\EnsureRole::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Throwable $exception, Request $request) {
            if (!$request->expectsJson() || $exception instanceof ValidationException) return null;

            $status = match (true) {
                $exception instanceof AuthenticationException => 401,
                $exception instanceof AuthorizationException => 403,
                $exception instanceof ModelNotFoundException => 404,
                $exception instanceof TokenMismatchException => 419,
                $exception instanceof HttpExceptionInterface => $exception->getStatusCode(),
                default => 500,
            };
            $messages = [
                400 => 'Permintaan tidak dapat diproses.',
                401 => 'Silakan masuk untuk melanjutkan.',
                403 => 'Anda tidak memiliki izin untuk melakukan tindakan ini.',
                404 => 'Data atau halaman yang diminta tidak ditemukan.',
                419 => 'Sesi halaman sudah berakhir. Muat ulang halaman, lalu coba kembali.',
                429 => 'Terlalu banyak permintaan. Tunggu sebentar sebelum mencoba kembali.',
                500 => 'Terjadi kendala pada server. Silakan coba kembali beberapa saat lagi.',
                503 => 'Layanan sedang diperbarui. Silakan coba kembali beberapa saat lagi.',
            ];
            $message = $status === 422 && $exception->getMessage() !== ''
                ? $exception->getMessage()
                : ($messages[$status] ?? $messages[500]);

            return response()->json(['message' => $message], $status);
        });
    })
    ->create();
