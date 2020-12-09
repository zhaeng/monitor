<?php
namespace api\components;

use common\models\OperateErrors;
use yii\base\UserException;

/**
 * 操作错误组件
 */
class OperateException extends UserException
{
    public function __construct($code = 0, \Exception $previous = null)
    {
        $message = operateErrors::getMessage($code);
        parent::__construct($message, $code, $previous);
    }

    public function getName()
    {
        return 'OperateError';
    }
}
