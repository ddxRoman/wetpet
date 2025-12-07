<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    

    // 🔥 Кастомный JSON ответ на ошибки валидации
protected function invalidJson($request, ValidationException $exception)
{
    return response()->json([
        'message' => 'Ошибка валидации',
        'errors' => $exception->errors(),
    ], $exception->status);
}

}
