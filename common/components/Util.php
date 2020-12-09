<?php


namespace common\components;


class Util
{
    /**
     * 获取签名
     * @param $params
     * @param $appKey
     * @param $appSecret
     * @param $time
     * @return string
     */
    public static function getSign($params, $appKey, $appSecret, $time = '')
    {
        $sign = '';
        if (!empty($params)) {
            unset($params['data_sign'],$params['sign']);
            ksort($params);
            $string = http_build_query($params);
            $str = $appKey . $string . $appSecret . $time;//echo $str;
            $result = md5($str);
            $sign = strtoupper($result);
        }
        return $sign;
    }
}