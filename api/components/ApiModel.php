<?php
namespace api\components;

use common\helpers\ExtendFunction;
use linslin\yii2\curl\Curl;
use Yii;
use yii\base\Model;
use yii\base\InvalidParamException;
use yii\helpers\Json;

/**
 * api模型
 * @author CGA
 */
class ApiModel extends Model
{

    /**
     * 获取模型错误
     */
    public static function getErrorMessge($model, $isthrow = true)
    {
        $error = null;
        if ($model->hasErrors()) {
            $errors = $model->getErrors();
            foreach ($errors as $value) {
                $error = $value[0];
                break;
            }
        }
        if ($isthrow && !(null === $error)) {
            throw new ValidateException($error);
        } elseif (!$isthrow && !(null === $error)) {
            return $error;
        } else {
            return true;
        }
    }

    /**
     * 抛出错误
     */
    protected static function throwError($code, $message = null)
    {
        if (null === $message) {
            throw new OperateException($code);
        } else {
            throw new ValidateException($message);
        }
    }

}
