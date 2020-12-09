<?php
namespace common\models;

/**
 * Class Server
 * @package common\models
 * @property int id
 * @property int uid
 * @property string name
 * @property string company
 * @property string remark
 * @property array config_info
 * @property int inner_ip
 * @property int out_ip
 * @property int port
 * @property int type
 * @property int status
 * @property int is_used
 * @property string extend_data
 * @property int sync
 * @property int block_time
 * @property int block_height
 * @property string hash
 * @property int created_at
 * @property int updated_at
 */
class Server extends \api\components\ActiveRecord
{
    const TYPE_NODE = 1;
    const TYPE_APP = 2;
    const TYPE_NODE_APP = 3;

    public static function tableName()
    {
        return 'monitor_server';
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