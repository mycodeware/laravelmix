<?php

namespace App\Logging;

use Monolog\Handler\RotatingFileHandler;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests; 
use Illuminate\Log\Writer;
use Illuminate\Contracts\Foundation\Application; 
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Lang,Response,Input;

class CustomWebLog
{
    /**
     * Customize the given logger instance.
     *
     * @param  \Illuminate\Log\Logger  $logger
     * @return void
     */
    public static function  createLog($request,$response=[])
    { 
        $log = [
                'requestUrl' => $request->url(),
                'hostName' => getHostName(),
                'class' => \Route::getCurrentRoute()->getActionName(),
                'fileLocation' =>  self::filePath().'.php',
                'routeName' => \Route::currentRouteName()
            ];
          
        if(empty($response)){
             $response = [
                 "code"=>404,
                    "status"=>'success',
                    "message"=>'no response available',
                    'data'=>[]
            ] ;
        } 

        $logFileName = CustomWebLog::controllerName().'_'.CustomWebLog::methodName().'.log';
        $logData = new Logger($log);
        $logData->pushHandler(new StreamHandler(storage_path('logs/web/'.date('Y-m-d').'/'.$logFileName)), Logger::INFO,true,0777);
        $logData->info(Response::json([$response]));
    }

    /*Get Controller Name*/
    public static function controllerName(){
        $routeArray = app('request')->route()->getAction();
        $controllerAction = class_basename($routeArray['controller']);
        list($controller, $action) = explode('@', $controllerAction);
        return $controller;
    }
    /*Get method Name*/
    public static function methodName(){
        $routeArray = app('request')->route()->getAction();
        $controllerAction = class_basename($routeArray['controller']);
        list($controller, $action) = explode('@', $controllerAction);
        return $action;
    }
     /*Get file path Name*/
    public static function filePath(){
        $routeArray = app('request')->route()->getAction();
        $controllerAction = ($routeArray['controller']);
        list($controller, $action) = explode('@', $controllerAction);
        return $controller;
    }
}