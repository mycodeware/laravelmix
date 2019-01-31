<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Http\Request;
use Response;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        MethodNotAllowedHttpException::class,
        NotFoundHttpException::class,
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
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    { 
        if(str_contains($request->path(),'api/v')){
            if($exception instanceof NotFoundHttpException){
               
               return response()->json([ "status"=>'failed','code'=>404,"message"=>"Invalid Url" ,'data' => [] ]);
            }
            if($exception instanceof MethodNotAllowedHttpException){
                return response()->json([ "status"=>'failed','code'=>405,"message"=>"Method not allowed" ,'data' => [] ]);
            }
        }

        return parent::render($request, $exception);
    }
}
