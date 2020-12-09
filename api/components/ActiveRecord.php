<?php


namespace api\components;

use yii\behaviors\TimestampBehavior;

class ActiveRecord extends \yii\db\ActiveRecord
{
    public function handleError(){
        $errors = $this->getFirstErrors();
        return [
            'code' => 202,
            'error' => reset($errors),
            'message' => reset($errors),
            //'data' => (object)[],
        ];
    }

    public $page = 1;
    public $pageSize = 50;


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    self::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    self::EVENT_BEFORE_UPDATE => ['updated_at'],
                ]
            ]
        ];
    }
}