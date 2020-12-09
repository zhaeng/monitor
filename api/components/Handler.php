<?php

/**
 * Created by PhpStorm.
 * User: libingke
 * Date: 2018/8/9
 * Time: 10:59
 */

namespace api\components;

use Yii;
use yii\base\ErrorException;
use yii\base\Exception;
use yii\base\UserException;
use yii\web\ErrorHandler;
use yii\web\Response;

class Handler extends ErrorHandler
{
    /**
     * renderException
     *
     * @author: libingke
     * @param \yii\base\Exception $exception
     */
    protected function renderException($exception)
    {
        if (Yii::$app->has('response')) {
            $response = Yii::$app->getResponse();
            $response->isSent = false;
            $response->stream = null;
            $response->data = null;
            $response->content = null;
        } else {
            $response = new Response();
        }

        $useErrorView = $response->format === Response::FORMAT_HTML && (!YII_DEBUG || $exception instanceof UserException);

        $response->setStatusCodeByException($exception);

        if ($useErrorView && $this->errorAction !== null) {
            $result = Yii::$app->runAction($this->errorAction);
            if ($result instanceof Response) {
                $response = $result;
            } else {
                $response->data = $result;
            }
            $response->send();
            return;
        }elseif($response->format === Response::FORMAT_HTML){
            if ($this->shouldRenderSimpleHtml()) {
                // AJAX request
                $data = '<pre>' . $this->htmlEncode(static::convertExceptionToString($exception)) . '</pre>';
            } else {
                // if there is an error during error rendering it's useful to
                // display PHP error in debug mode instead of a blank screen
                if (YII_DEBUG) {
                    ini_set('display_errors', 1);
                }
                $file = $useErrorView ? $this->errorView : $this->exceptionView;
                $data = $this->renderFile($file, [
                    'exception' => $exception,
                ]);
            }
        }else {
            $data = [
                'code' => $exception->getCode() ?: ($response->statusCode ?: 500),
                'message' => $exception->getMessage(),
                'error' => $exception->getMessage(),
                'data' => [],
            ];
        }

        $response->data = $data;
        $response->setStatusCode(200);
        $response->send();
    }
}