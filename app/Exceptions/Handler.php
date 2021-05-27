<?php

namespace App\Exceptions;

use App\Frame\Exceptions\CriticalException;
use App\Frame\Exceptions\DebugException;
use App\Frame\Exceptions\ErrorException;
use App\Frame\Exceptions\WarningException;
use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception $exception
     *
     * @return void
     */
    public function report(Exception $exception)
    {
        if ($exception instanceof ErrorException) {
            Log::error('ERROR - ' . $exception->getMessage());
        } elseif ($exception instanceof WarningException) {
            Log::warning('WARNING - ' . $exception->getMessage());
        } elseif ($exception instanceof DebugException) {
            Log::debug('DEBUG - ' . $exception->getMessage());
        } elseif ($exception instanceof CriticalException) {
            Log::critical('CRITICAL - ' . $exception->getMessage());
        }
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception               $exception
     *
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        return parent::render($request, $exception);
    }
}
