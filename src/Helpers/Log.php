<?php

namespace rhmdarif\Library\Helpers;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log
{
    public static function create($module, $ref, $request, $response)
    {
        $path = date("Y").'/'.date('m').'/'.date('d').'/'.auth()->user()->id.'/activity.log';
        // $content_log = $module."|".date("Y-m-d H:i:s")."|".$request."|".$response;
        $content_log = [
            "ref" => $ref,
            "request" => $request,
            "response" => $response
        ];

        $log = new Logger('log');
        $log->pushHandler(new StreamHandler(storage_path('app/logs/'.$path)), Logger::INFO);
        $log->info($module, $content_log);
    }

    public static function system($author, $module, $ref, $request, $response)
    {
        $path = date("Y").'/'.date('m').'/'.date('d').'/'.$author.'/system.log';
        // $content_log = $module."|".date("Y-m-d H:i:s")."|".$request."|".$response;
        $content_log = [
            "ref" => $ref,
            "request" => $request,
            "response" => $response
        ];

        $log = new Logger('log');
        $log->pushHandler(new StreamHandler(storage_path('app/logs/'.$path)), Logger::INFO);
        $log->info($module, $content_log);
    }

    public static function base($module, $ref, $request, $response)
    {
        $path = date("Y").'/'.date('m').'/'.date('d').'/system.log';
        // $content_log = $module."|".date("Y-m-d H:i:s")."|".$request."|".$response;
        $content_log = [
            "ref" => $ref,
            "request" => $request,
            "response" => $response
        ];

        $log = new Logger('log');
        $log->pushHandler(new StreamHandler(storage_path('app/logs/'.$path)), Logger::INFO);
        $log->info($module, $content_log);
    }

    public static function store($filename, $module, $ref, $request, $response)
    {
        $path = date("Y").'/'.date('m').'/'.date('d').'/'.$filename.'.log';
        // $content_log = $module."|".date("Y-m-d H:i:s")."|".$request."|".$response;
        $content_log = [
            "ref" => $ref,
            "request" => $request,
            "response" => $response
        ];

        $log = new Logger('log');
        $log->pushHandler(new StreamHandler(storage_path('app/logs/'.$path)), Logger::INFO);
        $log->info($module, $content_log);
    }
}
