<?php
namespace common\helpers;
use Yii;
/**
 * 自定义函数
 */
class ExtendFunction
{
    /**
     * 数组转换字符串
     * @param  array    $array 数组
     * @param  string   $glue  字符
     * @return string
     */
    public static function array2str($array, $glue)
    {
        $str = '';
        foreach ($array as $key => $value) {
            $str .= $key . '=' . $value . $glue;
        }
        $str = substr($str, 0, -1);
        return $str;
    }

    /**
     * 数据过滤
     */
    public static function _safeData($data)
    {
        if ($data) {
            if (is_array($data)) {
                $temp = [];
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $temp[$key] = self::_safeData($value);
                    } elseif (is_string($value)) {
                        $temp[$key] = htmlspecialchars(addslashes($value));
                    } else {
                        $temp[$key] = htmlspecialchars(addslashes((string) $value));
                    }
                }
            } else {
                $temp = htmlspecialchars(addslashes($data));
            }
        } else {
            $temp = [];
        }
        return $temp;
    }

    public static function getRiskSign($params, $appkey, $appSecret, $time)
    {
        $sign = '';
        if (!empty($params)) {
            if (isset($params['r'])) unset($params['r']);
            ksort($params);
            $string = http_build_query($params);
            $result = md5($appkey . $string . $appSecret . $time);
            $sign = strtoupper($result);
        }
        return $sign;
    }

    /**
     * 风控接口授权头部
     * @param $params
     * @return string
     */
    public static function getRiskHeader($params)
    {
        foreach ($params as $key => $v) {
            if (empty($v) && ($v !== 0 && $v !== '0')) {
                unset($params[$key]);
            }
        }
        if(empty($params)){
            $params['rr'] = rand(1,10);
        }
        $appkey = Yii::$app->params['riskAppkey'];
        $time = time();
        $header = array(
            'APP-KEY' => $appkey,
            'timestamp' => $time,
            'signature' => self::getRiskSign($params, $appkey, Yii::$app->params['riskAppsecret'], $time),
        );
        return array($header, $params);
    }
}
