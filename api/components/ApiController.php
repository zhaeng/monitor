<?php

namespace api\components;



use filsh\yii2\oauth2server\filters\auth\CompositeAuth;
use filsh\yii2\oauth2server\filters\ErrorToExceptionFilter;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use Yii;

/**
 * api控制器
 * @author ZCY
 */
class ApiController extends Controller
{


    protected function sendResponse($data = [])
    {

        if ($data === true || $data == null) {
            $data = [];
        }
        if (!is_array($data)) {
            //throw new \Exception(500, '返回数据必须是数组');
            $res = ['code' => 500, 'error' => 'response data must be array', 'message' => '返回数据必须是数组', 'data' => []];
        } else {
            $res = ['code' => 200, 'error' => '', 'message' => 'ok', 'data' => []];
        }
        return \Yii::$app->response->data = array_merge($res, $data);
    }


    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    ['class' => HttpBearerAuth::className()],
                    ['class' => QueryParamAuth::className(), 'tokenParam' => 'accessToken'],
                ]
            ],
            'exceptionFilter' => [
                'class' => ErrorToExceptionFilter::className()
            ],
            /*'access' => [
                'class' => 'api\components\AccessControl',
                'allowActions' => [
                    'login',
                ]
            ],*/
        ]);
    }
}
