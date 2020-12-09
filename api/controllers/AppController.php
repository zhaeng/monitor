<?php


namespace api\controllers;


use api\components\ApiController;
use api\models\App;
use api\models\Relation;

class AppController extends ApiController
{
    /**
     * 添加应用
     * @author zhaeng
     */
    public function actionAdd()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new App();
        $res = $model->addApp($params);
        $this->sendResponse($res);
    }

    /**
     * 添加应用
     * @author zhaeng
     */
    public function actionUpdate()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new App();
        $res = $model->updateApp($params);
        $this->sendResponse($res);
    }

    /**
     * 获取应用列表
     * @author zhaeng
     */
    public function actionList()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new App();
        $res = $model->search($params);
        $this->sendResponse($res);
    }

    /**
     * 通过app_key获取应用信息
     * @author zhaeng
     */
    public function actionInfo()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new App();
        $res = $model->getInfo($params);
        $this->sendResponse($res);
    }

    /**
     * 获取应用所属服务器
     * @author zhaeng
     */
    public function actionGetServerInfo()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new App();
        $res = $model->getServerInfo($params);
        $this->sendResponse($res);
    }

    public function actionDel()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new App();
        $res = $model->del($params);
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
        $this->sendResponse($res);
    }

    /**
     * 修改通知方式
     * @author zhaeng
     */
    public function actionChangeNotify()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new App();
        $res = $model->changeNotify($params);
        $this->sendResponse($res);
    }

    /**
     * 修改监控状态
     * @author zhaeng
     */
    public function actionChangeMonitor()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new App();
        $res = $model->changeMonitor($params);
        $this->sendResponse($res);
    }

    /**
     * 统计数据
     * @author zhaeng
     */
    public function actionStatics()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new App();
        $res = $model->statics($params);
        $this->sendResponse($res);
    }

    public function actionRelation()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new Relation();
        $res = $model->relation($params);
        $this->sendResponse($res);
    }
}