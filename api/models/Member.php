<?php


namespace api\models;


use common\models\Sms;
use common\models\User;

class Member extends User
{
    const SCENARIO_ACTIVE = 'active';
    const SCENARIO_LOGIN = 'login';

    public $verify_code;

    public function rules()
    {
        return [
            [['username', 'password', 'verify_code'], 'required', 'on' => self::SCENARIO_ACTIVE],
            ['username', 'checkUser', 'on' => [self::SCENARIO_ACTIVE]],
            [['username', 'password'], 'required', 'on' => self::SCENARIO_LOGIN],
            ['password', 'string', 'length' => [6, 16], 'on' => self::SCENARIO_ACTIVE],
            ['verify_code', 'checkCode', 'on' =>  self::SCENARIO_ACTIVE],
        ];
    }

    public function checkUser($attribute, $param)
    {
        $one = self::find()->where(['username' => $this->$attribute])->one();
        if (!$one) {
            $this->addError($attribute, '用户不存在');
        } else {
            if ($one['status'] == self::STATUS_DELETED) {
                $this->addError($attribute, '用户不可用');
                return;
            }
            /*if ($this->scenario == self::SCENARIO_ACTIVE && $one['status'] == self::STATUS_ACTIVE) {
                $this->addError($attribute, '用户已激活');
                return;
            }*/
        }
    }

    public function checkCode($attribute, $param)
    {
        if (!$this->hasErrors()) {
            $sms = new Sms();
            $type = '';
            if ($this->scenario == self::SCENARIO_ACTIVE) {
                $type = Sms::TYPE_ACTIVE;
                $one = self::findOne(['username' => $this->username]);
                if (!$one) {
                    $this->addError($this->$attribute, '用户不存在');
                    return;
                }
                $this->id = $one['id'];
            }

            $res = $sms->verifyCode($this->id, $this->$attribute, $type);
            if (!$res) {
                $this->addError($attribute, '验证码错误');
            }
        }
    }

    public function active($params)
    {
        $this->setScenario(self::SCENARIO_ACTIVE);
        $this->load(['Member' => $params]);
        if ($this->validate()) {
            $one = self::findOne(['username' => $this->username]);
            if (!$one)
                return ['code' => '202', 'error' => '用户不存在', 'message' => '用户不存在'];
            $hash = \Yii::$app->security->generatePasswordHash($this->password);

            $one['password_hash'] = $hash;
            $one['status'] = self::STATUS_ACTIVE;

            if ($one->save(false)) {
                return ['message' => 'OK'];
            }
            return ['code' => '202', 'error' => '信息保存失败', 'message' => '信息保存失败'];
        }
        return $this->handleError();
    }


    public $rememberMe;

    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            if (!$user){
                return ['code' => 202,'error' => 'username or password error','message' => '用户名或密码错误','data' => []];
            }
            \Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            /**
             * @var \filsh\yii2\oauth2server\Module $oauth2
             */
            $oauth2 = \Yii::$app->getModule('oauth2');
            //获取oauth框架的请求体,并覆盖其请求方式
            $request = $oauth2->getRequest();
            $request->request = array_merge(\Yii::$app->request->post(), ['grant_type' => 'password', 'client_id' => 'monitor', 'client_secret' => 'monitor']);
            $oauth2->set('request', $request);

            $oauthResponse = $oauth2->getServer()->handleTokenRequest()->getParameters();
            if (isset($oauthResponse['error']) && $oauthResponse['error'] == 'invalid_grant') {
                return ['code' => 202,'error' => 'username or password error','message' => '用户名或密码错误','data' => []];
            } else {
                return ['data' => $oauthResponse];
            }
        }
        return $this->handleError();

    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        /*if ($this->_user === null) {
            $this->_user = User::findByUsername($this->username);
        }*/
        return User::findByUsername($this->username);
    }
}