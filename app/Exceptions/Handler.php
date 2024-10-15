<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Redirect;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Maneja las excepciones de la aplicación.
     * Verifico si la excepción es de tipo NotFoundHttpException y redirijo a la página de inicio.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Throwable $exception
    */
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof NotFoundHttpException) {
            if ($request->is('admin/*')) {
                return Redirect::to('/admin');
            } else {
                return Redirect::to('/');
            }
        }

        return parent::render($request, $exception);
    }
}
