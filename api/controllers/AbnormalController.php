<?php


namespace api\controllers;
use api\models\Abnormal;

use api\components\ApiController;

class AbnormalController extends ApiController
{
    public function actionList()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new Abnormal();
        $res = $model->search($params);
        $this->sendResponse($res);
    }

    public function actionDeal()
    {
        $params = \Yii::$app->getRequest()->get();
        $model = new Abnormal();
        $res = $model->deal($params);
        $this->sendResponse($res);
    }
}