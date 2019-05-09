<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * 获取成功输出
     * @param array $data
     * @param string $tip
     * @param int $httpCode
     * @return mixed
     */
    public function success($data = array(), $tip = '请求成功', $httpCode = 200)
    {
        return response()->json(array(
            'status' => 200,
            'success' => false,
            'data' => $data,
            'tip' => $tip,
        ), $httpCode);
    }

    /**
     * 错误提示输出
     * @param $erroeCode
     * @param string $tip
     * @param int $httpCode
     * @return mixed
     */
    public function error($erroeCode, $tip = '请求失败', $httpCode = 400)
    {
        return response()->json(array(
            'status' => $httpCode,
            'success' => false,
            'error' => array(
                'code' => $erroeCode,
                'message' => trans("errorCode.{$erroeCode}"),
            ),
            'tip' => $tip,
        ), $httpCode);
    }
}
