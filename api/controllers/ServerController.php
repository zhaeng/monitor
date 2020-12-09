<?php


namespace api\controllers;


use api\components\ApiController;
use api\models\Server;

class ServerController extends ApiController
{
    /**
     * 添加服务器
     * @author zhaeng
     */
    public function actionAdd()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new Server();
        $res = $model->addServer($params);
        $this->sendResponse($res);
    }

    /**
     * 更新服务器
     * @author zhaeng
     */
    public function actionUpdate()
    {
        $params = \Yii::$app->getRequest()->post();
        $model = new Server();
        $res = $model->updateServer($params);
        $this->sendResponse($res);
    }

    /**
     * 删除服务器
     * @author zhaeng
     */
    public function actionDel()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new Server();
        $res = $model->del($params);
        $this->sendResponse($res);
    }

    /**
     * 服务器列表
     * @author zhaeng
     */
    public function actionList()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new Server();
        $res = $model->search($params);
        $this->sendResponse($res);
    }

    /**
     * 获取可用服务器列表
     * @author zhaeng
     */
    public function actionEnableList()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new Server();
        $res = $model->enableList($params);
        $this->sendResponse($res);
    }

}