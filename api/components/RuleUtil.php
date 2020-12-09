<?php
namespace api\components;

use Yii;
use yii\base\Object;

/**
 * 验证规则组件
 */
class RuleUtil
{
    /**
     * 验证手机号
     * @param unknown $mobile
     */
    public static function checkMobile($mobile)
    {
        if (preg_match("/^1[34578]{1}\d{9}$/", $mobile)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * 校验证件号码
     * @param unknown $idcard
     * @return boolean
     */
    public static function checkIdNum($idcard)
    {
        if (strlen($idcard) != 18) {
            return false;
        }
        $idcard_base = substr($idcard, 0, 17);
        $verify_code = substr($idcard, 17, 1);
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $total = 0;
        for ($i = 0; $i < 17; $i++) {
            $total += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $total % 11;
        if ($verify_code == $verify_code_list[$mod]) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 验证国家代码
     * @param integer $sname 国家代码
     */
    public static function checkCountry($sname)
    {
        $apiModel=new ApiModel();
        $url = Yii::$app->params['basic_info']['url'] . Yii::$app->params['basic_info']['method']['countryInfo'] . $sname;
        $result = $apiModel->curl($url, [], [], 'get');
        if(isset($result['code']) && $result['code'] === 200){
            return [true, $result['data']];
        }else{
            return [false, $result['message']];
        }
    }

    /**
     * 验证省份代码
     * @param integer $sname 省份代码
     */
    public static function checkProvince($code)
    {
        $apiModel=new ApiModel();
        $url = Yii::$app->params['basic_info']['url'] . Yii::$app->params['basic_info']['method']['areaInfo'] . $code;
        $result = $apiModel->curl($url, [], [], 'get');
        if(isset($result['code']) && $result['code'] === 200 && $result['data']['level'] == 1){
            return [true, $result['data']];
        }else{
            return [false, '省份编号不存在'];
        }
    } 

    /**
     * 验证银行代码
     * @param integer $code 银行代码
     */
    public static function checkBank($code)
    {
        $model = new ApiModel();
        $url = Yii::$app->params['basic_info']['url'] . Yii::$app->params['basic_info']['method']['bankInfo'] . $code;
        $result = $model->curl($url, [], [], 'get');
        if(isset($result['code']) && $result['code'] === 200){
            return [true, $result['data']];
        }else{
            return [false, $result['message']];
        }
    }
    

    
}
