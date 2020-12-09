<?php

namespace common\models;

/**
 * Class App
 * @package common\models
 * @property int id
 * @property int uid
 * @property string app_key
 * @property string app_secret
 * @property string name
 * @property string en_name
 * @property string version
 * @property string status
 * @property string company
 * @property array server_ids
 * @property array manager
 * @property string extend_data
 * @property int created_at
 * @property int updated_at
 */
class App extends \api\components\ActiveRecord
{

    const STATUS_DEL = -1;
    const STATUS_MONITOR = 1;
    const STATUS_NOT_MONITOR = 0;

    public static function tableName()
    {
        return 'monitor_app';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'app_key' => 'App Key',
            'app_secret' => 'App Secret',
            'name' => '项目名称',
            'en_name' => '英文名称',
            'version' => '版本信息',
            'status' => '状态',
            'company' => '所属公司',
            'server_ids' => '涉及服务器',
            'manager' => '管理者',
            'extend_data' => '附加数据',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}