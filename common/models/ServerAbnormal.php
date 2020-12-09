<?php
namespace common\models;

/**
 * Class Server
 * @package common\models
 * @property int id
 * @property int server_id
 * @property string name
 * @property int inner_ip
 * @property int out_ip
 * @property int status
 * @property int block_time
 * @property int block_height
 * @property string hash
 * @property int created_at
 * @property int updated_at
 */
class ServerAbnormal extends \api\components\ActiveRecord
{

    public static function tableName()
    {
        return 'monitor_server_abnormal';
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'config_info' => '配置信息',
            'inner_ip' => '内网ip',
            'out_ip' => '外网ip',
            'port' => '监听端口',
            'type' => '类型',
            'status' => '状态',
            'extend_data' => '其他数据',
            'created_at' => '创建时间',
            'updated_at' => '更新时间',
        ];
    }
}