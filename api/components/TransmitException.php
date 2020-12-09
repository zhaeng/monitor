<?php
namespace api\components;
use yii\base\UserException;

/**
 * 验证错误组件
 */
class TransmitException extends UserException
{
    public function __construct($message = null , \Exception $previous = null)
    {
        parent::__construct($message, 400, $previous);
    }

    public function getName()
    {
        return 'TransmitError';
    }
}
