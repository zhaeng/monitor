<?php

namespace console\models;

use api\models\Server;
use common\models\ServerAbnormal;
use common\models\Sms;

class App extends \common\models\App
{
    public function check()
    {
        $apps = self::find()->select('id,app_key,name,server_ids,manager')->where(['status' => 1])->all();
        $server_ids = [];
        $result = $notice = [];
        /**
         * @var \common\models\App $one
         */
        foreach ($apps as $one) {
            $server = Server::find()
                ->select('id as server_id,name as server_name,inner_ip,out_ip,block_time,block_height,hash,updated_at')
                ->where(['id' => $one->server_ids, 'type' => [1,3]])
                ->orderBy('block_height desc')
                ->asArray()
                ->all();
            $ids = array_column($server,'server_id');
            sort($ids);
            $str = implode('-',$ids);
            if (isset($server_ids[$str])) continue;
            $server_ids[$str] = 1;
            $hashes = [];
            foreach ($server as $val) {
                if (isset($hashes[$val['hash']])) {
                    $hashes[$val['hash']] ++;
                } else {
                    $hashes[$val['hash']] = 1;
                }
            }
            //正统hash
            $hash = $server ? $server[0]['hash'] : '';
            if (count($hashes) > 1) {
                $c = max($hashes);
                $hash = array_search($c,$hashes);
            }

            $height = $server ? $server[0]['block_height'] : 0;

            $max_time = \Yii::$app->params['max_time_diff'] ?? 600;
            $max_height = \Yii::$app->params['max_height_diff'] ?? 10;
            $ids = ServerAbnormal::find()->select('server_id')->where(['status' => 0])->column();
            foreach ($server as $key => &$val) {
                if (in_array($val['server_id'],$ids))continue;
                $flag = false;
                $val['remark'] = '';
                /*if ($diff = ($height - $val['block_height']) > $max_height) {//高度落后
                    $val['remark'] = "高度落后$diff;";
                    $flag = true;
                }

                if ($val['block_height'] == $height && $hash && $val['hash'] != $hash) {//同一高度不通hash，分叉
                    $val['remark'] .= '同一高度不同hash;';
                    $flag = true;
                }
                if ($diff = (time() - $val['updated_at']) > $max_time) {//服务器脚本没请求接口
                    $val['remark'] .= $diff / 60 . ' min数据未更新';
                    $flag = true;
                }*/
                if (($diff = time() - $val['updated_at']) > $max_time) {
                    $val['remark'] = "节点信息" . floor($diff / 60) . "min未更新";
                    $flag = true;
                }
                if (!$flag && ($diff = time() - $val['block_time']) > $max_time) {
                    $val['remark'] = "节点" . floor($diff / 60) . "min未更新";
                    $flag = true;
                }
                if (!$flag && ($diff = $height - $val['block_height']) > $max_height) {
                    $val['remark'] = "落后{$diff}个区块";
                    $flag = true;
                }
                if (!$flag && $val['block_height'] == $height && $hash && $val['hash'] != $hash) {
                    $val['remark'] = "相同高度不同hash";
                    $flag = true;
                }
                if ($flag) {
                    $val['status'] = 0;
                    $val['app_id'] = $one->id;
                    $val['app_key'] = $one->app_key;
                    $val['app_name'] = $one->name;
                    $val['created_at'] = time();
                    $val['updated_at'] = time();
                    $result[] = $val;
                    $notice[] = [
                        'manager' => $one->manager,
                        'app_name' => $one->name,
                        'inner_ip' => long2ip($val['inner_ip']),
                        'out_ip' => long2ip($val['out_ip']),
                        'remark' => $val['remark']
                    ];
                }
            }

            if ($result){
                \Yii::$app->getDb()->createCommand()->batchInsert(ServerAbnormal::tableName(),array_keys($result[0]),$result)->execute();
            }
            //信息通知
            foreach ($notice as $v){
                $con = $v['app_name'] . '项目' . '内网ip:' . $v['inner_ip'] . ',外网ip:' . $v['out_ip'] . $v['remark'];
                foreach ($v['manager'] as $m){
                    if ($m['notify_type'] == 1) {//都通知
                        $this->_sendSms($m['mobile'],$con);
                        $this->_sendMail($m['email'],$con);
                    } elseif ($m['notify_type'] == 2) {//手机通知
                        $this->_sendSms($m['mobile'],$con);
                    } elseif ($m['notify_type'] == 3) {//邮件通知
                        $this->_sendMail($m['email'],$con);
                    }
                }
            }
        }
    }

    private function _sendMail($email,$body)
    {
        \Yii::$app->mailer->compose()
            ->setTo($email)
            ->setHtmlBody($body)
            ->setSubject('node monitor')
            ->send();
    }

    private function _sendSms($mobile,$con)
    {
        (new Sms(['type' => Sms::TYPE_NOTICE]))->sendMobile($mobile,$con);
    }
}