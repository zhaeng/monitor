<?php
namespace common\helpers;
use linslin\yii2\curl\Curl;
use yii\helpers\Json;

/**
 * 自定义函数
 */
class Extension
{
    public static function msectime()
    {
        list($msec, $sec) = explode(' ', microtime());
        $msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
        return $msectime;
    }

    /**
     * curl访问
     */
    public static function curl($url, $params = [], $header = [], $method = 'post',$timeout=10)
    {
        $curl = new Curl();
        $result = $curl
            ->setHeaders($header)
            ->setOption(CURLOPT_TIMEOUT, $timeout)
            ->setOption(CURLOPT_SSL_VERIFYPEER, false)
            ->setOption(CURLOPT_SSL_VERIFYHOST, false);
        if ($method == 'post') {
            $result = $result->setPostParams($params)->post($url);
        } else {
            $result = $result->setGetParams($params)->get($url);
        }
        $result = Json::decode($result);
        return $result;
    }

    /**
     * 获取一年总天数
     * @return  [int] 天数
     */
    public static function getDays()
    {
        return date('L', strtotime(time()))?366:365;
    }
    
    /**
     * 获取指定年月的上个月月初和月末时间戳
     * @return 
     */
    public static function getMonthBeginEndTime($date)
    {
        $thisyear = date('Y',strtotime($date));
        $thismonth = date('m',strtotime($date));

        if ($thismonth == 1) {
            $lastmonth = 12;
            $lastyear = $thisyear - 1;
        } else {
            $lastmonth = $thismonth - 1;
            $lastyear = $thisyear;
        }
        $lastStartDay = $lastyear . '-' . $lastmonth . '-1';
        $lastEndDay = $lastyear . '-' . $lastmonth . '-' . date('t', strtotime($lastStartDay));
        $b_time = strtotime($lastStartDay);               //上个月的月初时间戳
        $e_time = strtotime($lastEndDay)+3600*24-1;                 //上个月的月末时间戳
        if($lastmonth<10){
            $lastmonth='0'.$lastmonth;
        }
        $lastdate=$lastyear.'-'.$lastmonth.'-'.'01';
        return [$lastdate,$b_time,$e_time];
    }
    
    
    public static function getWeekBeginEndTime($date)
    {
        /*
               这里首先判断是不是周一，date('w')如果为0代表星期日,1就代表星期一，如果是的话获取周一的unix时间戳(从某个时间点到现在的秒数)
               如果不是周一，那么就获取最近的周一（过去的）的时间
         */
        $now=$date;
        $nowtime=strtotime($now);
        //var_dump($time);die;
        $time = ('1' == date('w',$nowtime)) ? strtotime('Monday',$nowtime) : strtotime('last Monday', $nowtime);
        
        //下面2句就是将上面得到的时间做一个起止转换
        
        //得到本周开始的时间，时间格式为：yyyy-mm-dd hh:ii:ss 的格式
        $beginTime = date('Y-m-d 00:00:00', $time-3600*24*7);
        
        //得到本周末最后的时间
        $endTime = date('Y-m-d 23:59:59', strtotime('Sunday', $nowtime)-3600*24*7);
      
        $lastdate=date('Y-m-d',strtotime($beginTime));
        $b_time=strtotime($beginTime);
        $e_time=strtotime($endTime);
        return [$lastdate,$b_time,$e_time];
    }

    public static function getDayBeginEndTime($date)
    {
        $now=strtotime($date);
        $lastdate=date('Y-m-d',$now-3600*24);
        $b_time=strtotime($lastdate);
        $e_time=$b_time+3600*24-1;
        return [$lastdate,$b_time,$e_time];
    }
    
    
    
}
