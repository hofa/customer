<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
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
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function render($request, Exception $e)
    {

        //框架自带数据验证错误信息返回处理
        if ($e instanceof \Illuminate\Validation\ValidationException) {
            $errors = $e->errors();
            return $this->errorEcho($e->status, reset($errors)[0]);
        }

        if (method_exists($e, 'getStatusCode')) {
            //json返回 http错误提示信息
            $http_code = $e->getStatusCode();
            switch ($http_code) {
                case $http_code >= 400:
                    return $this->errorEcho($e->getStatusCode(), $e->getMessage());
                    break;
                default:
                    return parent::render($request, $e);
            }
        }

        return parent::render($request, $e);

    }

    /**
     * 自定义输出格式
     * @param  $httpCode
     * @param  $msg
     * @return \Illuminate\Http\Response
     */
    private function errorEcho($httpCode, $msg = '')
    {
        if ($msg == '') {
            $msg = trans("httpCode.{$httpCode}");
        }
        return response()->json(array(
            'status' => $httpCode,
            'success' => false,
            'error' => array(
                'code' => $httpCode,
                'message' => $msg,
            ),
        ), $httpCode);
    }

}
