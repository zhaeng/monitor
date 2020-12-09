<?php


namespace common\models;


use api\components\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * Class Relation
 * @package common\models
 * @property int id
 * @property int app_id
 * @property string app_name
 * @property int server_id
 * @property string server_name
 * @property int inner_ip
 * @property int out_ip
 * @property int created_at
 * @property int updated_at
 */
class Relation extends ActiveRecord
{
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

    public static function tableName()
    {
        return 'monitor_relation';
    }

    public function getName()
    {
        return $this->hasOne(App::class,['id' => 'app_id'])->select('name,id');
    }
}