<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2019/3/18
 * Time: 15:36
 */

namespace common\components;


use yii\base\Component;

class SmsServer extends Component
{
    public $sign_id;
    public $country;
    public $app_key;
    public $app_secret;
    public $url;
    public $isBatch;

    /**
     * 发送短信
     * @param string $param
     * @param array $mobiles
     * @param array $tpl_id
     * @param array $code_type
     * @return array
     */
    public function sendBatchSms($mobiles,$param,$code_type,$tpl_id = 0)
    {
        //$isBatch = \Yii::$app->params['smsParam']['isBatch'];
        $result['code'] = 202;
        if ($this->isBatch) {
            $mobile = implode(',', $mobiles);
            $result = $this->sendSms($mobile,$param,$tpl_id,$code_type);
        } else {
            foreach ($mobiles as $mobile) {
                $result = $this->sendSms($mobile,$param,$tpl_id,$code_type);
            }
        }
        return $result;
    }

    /**
     *
     * @author zhaeng
     * @param $mobile
     * @param $param
     * @param $tpl_id
     * @param $code_type
     * @return array
     */
    public function sendSms($mobile,$param,$code_type,$tpl_id = 0)
    {
        $time = time();
        //$smsParams = \Yii::$app->params['smsParam'];
        $params = [
            'param' => $param,
            'mobile' => $mobile,
            'tplid' => $tpl_id,
            'codetype' => $code_type,
            'signid' => $this->sign_id,
            'country' => $this->country
        ];
        $sign = Util::getSign($params, $this->app_key, $this->app_secret, $time);
        $headers = [
            'FZM-Ca-Timestamp: ' . $time,
            'FZM-Ca-AppKey: '. $this->app_key,
            'FZM-Ca-Signature: ' . $sign,
        ];
        $curl = new CUrl();
        $curl->setHeader($headers);
        $curl->setData($params);
        $result = $curl->post($this->url, 'http','json');
        if (isset($result['code']) && $result['code'] == 200) {
            return [true,'OK'];
        }
        return [false,$result['message'] ?? 'send sms fail'];

    }

}