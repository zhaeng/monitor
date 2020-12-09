<?php


namespace console\controllers;


use common\models\Sms;
use console\models\App;
use yii\console\Controller;

class CheckController extends Controller
{
    public function actionIndex()
    {
        $app = new App();
        $app->check();
    }
}