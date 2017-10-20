<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/16
 * Time: 下午11:51
 */

require dirname(__DIR__) . '/../../vendor/autoload.php';

$dispatcher = new \Inhere\Middleware\Dispatcher(
    function ($request, $handler) {
        echo 'before';
        $res = $handler($request);
        echo 'after';

        return $res;
    }
);
