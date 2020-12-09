<?php
namespace api\controllers;

use api\models\App;
use api\models\Member;
use api\models\Server;
use common\models\Sms;
use yii\web\Controller;

/**
 * 错误处理控制器
 */
class IndexController extends Controller
{
    public function actionIndex()
    {
        return ['code' => 200,'error' => '','message' => 'monitor system api','data' => []];
    }

    public function beforeAction($action)
    {
        $actions = ['index/update-data','index/update-server-info','index/get-server-list','index/update-app-service'];
        //$actions = [];
        $controller = $action->controller->id;
        $route = $controller . '/' . $action->id;
        if (in_array($route,$actions)) {
            $params = \Yii::$app->getRequest()->post();
            if (!isset($params['app_key']) || !isset($params['sign']) || !isset($params['rand'])) {
                \Yii::$app->response->data = [
                    'code' => 203,
                    'error' => '参数不完整',
                    'message' => '参数不完整',
                    'data' => []
                ];
                return false;
            }
            $one = App::findOne(['app_key' => $params['app_key']]);
            if (!$one) {
                \Yii::$app->response->data = [
                    'code' => 203,
                    'error' => 'app key error',
                    'message' => 'app key error',
                    'data' => []
                ];
                return false;
            }
            if (md5($one->app_key . '_' . $one->app_secret . '_' . $params['rand']) != $params['sign']) {
                \Yii::$app->response->data = [
                    'code' => 203,
                    'error' => 'data sign error',
                    'message' => '数据签名错误',
                    'data' => []
                ];
                return false;
            }
        }
        return parent::beforeAction($action);
    }

    /**
     * 更新服务器信息 shell脚本调用
     * @author zhaeng
     */
    public function actionUpdateData()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new Server();
        $res = $model->updateData($params);
        $this->sendResponse($res);
    }

    /**
     * 更新服务器信息 shell脚本调用
     * @author zhaeng
     */
    public function actionUpdateAppService()
    {
        $params = \Yii::$app->getRequest()->post();
        //$model = new Server();
        //$res = $model->updateData($params);
        $res = is_array($params) ? json_encode($params) : $params;
        file_put_contents(dirname(__DIR__) . '/runtime/logs/application.log',$res . PHP_EOL,FILE_APPEND);
        $this->sendResponse($res);
    }

    /**
     * 更新服务器硬件信息 shell脚本调用
     * @author zhaeng
     */
    public function actionUpdateServerInfo()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new Server();
        $res = $model->updateServerInfo($params);
        $this->sendResponse($res);
    }

    /**
     * 获取应用所属服务器列表 shell脚本调用
     * @author zhaeng
     */
    public function actionGetServerList()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new App();
        $res = $model->getServerList($params);
        return $this->sendResponse($res);
    }

    /**
     * 激活
     * @return array
     * @author zhaeng
     */
    public function actionActive()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new Member();
        $res = $model->active($params);
        return $this->sendResponse($res);
    }

    /**
     * 登录
     * @author zhaeng
     */
    public function actionLogin()
    {
        $params = \Yii::$app->request->post();
        $model = new Member();
        $model->setScenario(Member::SCENARIO_LOGIN);
        $model->load(['Member' => $params]);
        $res = $model->login();
        $this->sendResponse($res);
    }

    /**
     * 发送验证码
     * @return array
     * @author zhaeng
     */
    public function actionSend()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new Sms();
        $model->setScenario(Sms::TYPE_ACTIVE);
        $res = $model->sendSms($params);
        return $this->sendResponse($res);
    }


    protected function sendResponse($data = [])
    {
        if ($data === true || $data == null) {
            $data = [];
        }
        if (!is_array($data)) {
            $res = ['code' => 500, 'error' => 'response data must be array', 'message' => '返回数据必须是数组', 'data' => []];
        } else {
            $res = ['code' => 200, 'error' => '', 'message' => 'ok', 'data' => []];
        }
        return \Yii::$app->response->data = array_merge($res, $data);

    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            /*'index' => [
                'class' => 'yii\web\ErrorAction',
            ],*/
        ];
    }

}
