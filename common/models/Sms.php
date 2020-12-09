<?php
/**
 * Created by PhpStorm.
 * User: zhang
 * Date: 2019/3/21
 * Time: 10:05
 */

namespace common\models;


use yii\base\Model;

class Sms extends Model
{
    public $type;
    public $uid;
    public $username;
    public $is_notify = false;

    const PRE = 'sms';

    const TYPE_LOGIN = 'login';//登录
    const TYPE_ACTIVE = 'activate';//激活
    const TYPE_NOTICE = 'notice';//激活

    public $types = [
        'notice',
        'activate'
    ];

    /* public function init()
     {
         parent::init();
         $this->types = \Yii::$app->params['sms-type'] ?? [];
     }*/


    public function rules()
    {
        return [
            ['type', 'required'],
            //['type','in','range' => [self::TYPE_ACTIVE,self::TYPE_LOGIN,self::TYPE_ADD_ADDRESS,self::TYPE_ADD_PAY,self::TYPE_WITHDRAW]],
            ['username', 'required', 'on' => self::TYPE_ACTIVE],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type' => '类型',
            'uid' => 'uid',
            'username' => '用户名'
        ];
    }

    /**
     * 发送验证码
     * @param $params
     * @return array
     * @author zhaeng
     */
    public function sendSms($params)
    {
        $this->load(['Sms' => $params]);
        if ($this->validate()) {
            if ($this->scenario == self::TYPE_ACTIVE) {
                $user = User::findOne(['username' => $this->username]);
                $this->uid = $user ? $user->id : null;
            }
            if (!$this->uid) {
                return ['code' => 202, 'error' => '用户不存在', 'message' => '用户不存在'];
            }
            list($flag, $msg) = $this->send();
            if ($flag) {
                return ['message' => 'OK'];
            }
            return ['code' => 202, 'error' => $msg, 'message' => $msg];
        }
        $errors = $this->getFirstErrors();
        return [
            'code' => 202,
            'error' => reset($errors),
            'message' => reset($errors),
            'data' => [],
        ];
    }


    /**
     * 发送短信
     * @param string $con
     * @return array
     * @author zhaeng
     */
    public function send($con = '')
    {
        $res = true;
        if (!$this->is_notify) {
            $con = rand(100000, 999999);
            $con = 6666;
            $cache = \Yii::$app->cache;
            $res = $cache->set($this->_getPre() . $this->uid, $con, 300);
        }
        $flag = false;
        $error = '短信发送失败';
        if ($res) {
            /**
             * @var $sms \common\components\SmsServer
             */
            $sms = \Yii::$app->get('sms');
            //return [true,'ok'];
            if (!in_array($this->type,$this->types)) {
                $error = '短信类型错误';
            } else {
                list($flag, $error) = $sms->sendSms($this->username, $con, $this->type);
            }
        }
        return [$flag, $error];
    }

    /**
     * 发送短信
     * @param string $mobile
     * @param string $con
     * @return array
     * @author zhaeng
     */
    public function sendMobile($mobile, $con)
    {
        $flag = false;
        /**
         * @var $sms \common\components\SmsServer
         */
        $sms = \Yii::$app->get('sms');
        if (!in_array($this->type,$this->types)) {
            $error = '短信类型错误';
        } else {
            list($flag, $error) = $sms->sendSms($mobile, $con, $this->type);
        }

        return [$flag, $error];
    }

    /**
     * 验证验证码
     * @param $uid
     * @param $code
     * @param $type
     * @return bool
     * @author zhaeng
     */
    public function verifyCode($uid, $code, $type = '')
    {
        //return true;
        $cache = \Yii::$app->cache;
        $res = $cache->get($this->_getPre($type) . $uid);
        if ($res && $code == $res) {
            return true;
        }
        return false;
    }

    /**
     * 获取前缀
     * @param $pre
     * @return string
     * @author zhaeng
     */
    private function _getPre($pre = '')
    {
        $pre = $pre ? $pre : $this->type;
        if (strpos($pre, 'pwd')) {
            $pre = 'password';
        } elseif (strpos($pre, 'payment')) {
            $pre = 'payment';
        }

        return self::PRE . $pre;
    }
}