<?php

namespace api\models;


use common\models\Relation;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

class App extends \common\models\App
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_CHANGE = 'change';
    const SCENARIO_INFO = 'info';
    const SCENARIO_DEL = 'del';
    const SCENARIO_GET_SERVER = 'get-server';
    const SCENARIO_GET_SERVER_LIST = 'get-server-list';
    const SCENARIO_CHANGE_NOTIFY = 'change-notify';
    const SCENARIO_CHANGE_MONITOR = 'change-monitor';

    public $label;
    public $notify_type;//0不通知，1手机和邮箱，2 手机 ，3邮箱

    public $servers;

    public $search;

    public function rules()
    {
        return [
            ['id', 'required', 'on' => [self::SCENARIO_CHANGE, self::SCENARIO_DEL]],
            [['page', 'pageSize', 'search'], 'safe'],
            [['name', 'en_name', 'company', 'version', 'status', 'server_ids', 'manager'], 'required', 'on' => [self::SCENARIO_CHANGE, self::SCENARIO_ADD]],
            ['status', 'in', 'range' => [0, 1], 'on' => [self::SCENARIO_CHANGE, self::SCENARIO_ADD]],
            [['name', 'en_name', 'company', 'version'], 'string', 'min' => 2, 'max' => 30, 'on' => [self::SCENARIO_CHANGE, self::SCENARIO_ADD]],
            ['manager', 'checkManager', 'on' => [self::SCENARIO_CHANGE, self::SCENARIO_ADD]],
            ['server_ids', 'checkServer', 'on' => [self::SCENARIO_CHANGE, self::SCENARIO_ADD]],
            [['app_key', 'app_secret'], 'required', 'on' => self::SCENARIO_INFO],
            [['label', 'notify_type', 'app_key'], 'required', 'on' => self::SCENARIO_CHANGE_NOTIFY],
            ['notify_type', 'in', 'range' => [0, 1, 2, 3], 'on' => self::SCENARIO_CHANGE_NOTIFY],
            [['status', 'app_key'], 'required', 'on' => self::SCENARIO_CHANGE_MONITOR],
            ['status', 'in', 'range' => [0, 1], 'on' => self::SCENARIO_CHANGE_MONITOR],
            ['app_key', 'required', 'on' => [self::SCENARIO_GET_SERVER, self::SCENARIO_GET_SERVER_LIST]],
            [['sign', 'rand'], 'required', 'on' => self::SCENARIO_GET_SERVER_LIST]
        ];
    }

    public function checkManager($attribute, $params)
    {
        if (!$this->hasErrors()) {
            if (!$this->$attribute) $this->addError($attribute, '管理者格式错误');
            $result = [];
            foreach ($this->$attribute as $att) {
                if (!isset($att['name'])) {
                    $this->addError($attribute, '管理者名称不能为空');
                    return;
                }
                if (strlen($att['name']) > 10) {
                    $this->addError($attribute, '管理者名称不能超过十个字符');
                    return;
                }
                if (!isset($att['email'])) {
                    $this->addError($attribute, '管理者邮箱不能为空');
                    return;
                }
                if (!preg_match('/(([a-z0-9]*[-_]?[a-z0-9]+[-_.]?)*@([a-z0-9]*[-_]?[a-z]+)+[\.][a-z0-9]{2,3}([\.][a-z]{1,3})?)/', $att['email'], $res)) {
                    $this->addError($attribute, '管理者邮箱格式错误');
                    return;
                }
                if (!isset($att['mobile'])) {
                    $this->addError($attribute, '管理者手机号不能为空');
                    return;
                }
                if (!preg_match('/^[1][35789][0-9]{9}$/', $att['mobile'], $res)) {
                    $this->addError($attribute, '管理者手机号格式错误');
                    return;
                }
                $tag = substr(md5($att['mobile'] . $att['name'] . $att['email']), 8, 6);
                $att['label'] = $tag;
                $att['notify_type'] = 0;
                $result[$tag] = $att;
            }
            $this->$attribute = $result;
        }
    }

    public function checkServer($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $relation = Relation::find()
                ->select('app_name,server_name')
                ->where(['server_id' => $this->$attribute])
                ->asArray()
                ->all();
            if (!$relation) {
                $this->servers = Server::find()
                    ->select('id,name,inner_ip,out_ip')
                    ->where(['id' => $this->$attribute])
                    ->asArray()
                    ->all();
                if (count($relation) != count($this->$attribute)) {
                    $this->addError($attribute,'服务器信息错误');
                }
            } else {
                $this->addError($attribute,$relation[0]['app_name'] . '项目正在使用服务器' . $relation[0]['server_name']);
            }
        }
    }

    public function addApp($params)
    {
        $this->setScenario(self::SCENARIO_ADD);
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $this->uid = \Yii::$app->getUser()->id;
            $this->app_key = $this->_rand();
            $this->app_secret = $this->_rand();
            //$this->server_ids = array_unique($this->server_ids);
            $this->save(false);
            $relation = [];
            foreach ($this->servers as $server) {
                $relation[] = [$this->id, $this->name, $server->id,$server->name, $server->inner_ip, $server->out_ip, time(), time()];
            }
            Server::updateAll(['is_used' => 1,['id' => $this->server_ids]]);
            $this->_addRelation($relation);
            return ['code' => 200, 'data' => []];
        }
        return $this->handleError();
    }

    public function updateApp($params)
    {
        $this->setScenario(self::SCENARIO_CHANGE);
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $one = self::findOne($this->id);
            if (!$one)
                return ['code' => 202, 'data' => [], 'message' => 'data not fund', 'error' => '数据不存在'];
            Server::updateAll(['is_used' => 0,['id' => $one->server_ids]]);
            Relation::deleteAll(['app_id' => $one->id]);
            foreach ($this->getAttributes() as $key => $val) {
                if ($val)
                    $one->$key = $val;
            }
            //$one->server_ids = array_unique($one->server_ids);
            $one->save(false);
            Server::updateAll(['is_used' => 1,['id' => $one->server_ids]]);
            $relation = [];
            foreach ($one->servers as $server) {
                $relation[] = [$this->id, $one->name, $server->id,$server->name, $server->inner_ip, $server->out_ip, time(), time()];
            }
            $this->_addRelation($relation);
            return ['code' => 200, 'data' => []];
        }
        return $this->handleError();
    }

    public function search($params)
    {
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $query = self::find()
                ->select('id,app_key,app_secret,name,en_name,server_ids,company,version,status,manager,created_at,updated_at')
                ->where(['>', 'status', self::STATUS_DEL])->orderBy('status desc');
            if (!empty($this->search)) {
                if (is_numeric($this->search)) {
                    $query->where(['id' => (int)$this->search]);
                } elseif (ctype_alnum($this->search)) {
                    $query->where(['like', 'app_key', $this->search]);
                } else {
                    $query->where(['like', 'name', $this->search]);
                }
            }
            $provider = new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'id' => SORT_ASC,
                    ]
                ],
                'pagination' => [
                    'pageSize' => $this->pageSize,
                    'page' => $this->page - 1
                ]
            ]);
            $models = $provider->getModels();
            array_walk($models, function ($item) {
                $item['manager'] = array_values($item['manager']);
                $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
                $item['updated_at'] = date('Y-m-d H:i:s', $item['updated_at']);
            });
            return ['code' => 200, 'data' => ['list' => $models, 'total' => $provider->getTotalCount()]];
        }
        return $this->handleError();
    }

    public function getInfo($params)
    {
        $this->setScenario(self::SCENARIO_INFO);
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $data = self::find()
                ->select('app_key,app_secret,name,server_ids')
                ->where(['app_key' => $this->app_key, 'app_secret' => $this->app_secret])
                ->one();
            if (empty($data))
                return ['code' => 202, 'error' => 'data not fund', 'message' => '数据未找到'];
            $server = Server::find()
                ->select('inner_ip,out_ip,port,type')
                ->where(['id' => $data['server_ids']])
                ->asArray()
                ->all();

            array_walk($server, function (&$item) {
                $item['inner_ip'] = long2ip($item['inner_ip']);
                $item['out_ip'] = long2ip($item['out_ip']);
            });

            return [
                'code' => 200,
                'data' => [
                    'app_key' => $data['app_key'],
                    'app_secret' => $data['app_secret'],
                    'name' => $data['name'],
                    'server' => $server
                ]
            ];
        }
        return $this->handleError();
    }

    public function getServerInfo($params)
    {
        $this->setScenario(self::SCENARIO_GET_SERVER);
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $one = self::findOne(['app_key' => $this->app_key]);
            if (!$one)
                return ['code' => 202, 'error' => 'data not fund', 'message' => '数据未找到'];
            $server = Server::find()
                ->select('name,inner_ip,out_ip,block_height,block_time,hash,updated_at')
                ->where(['id' => $one->server_ids])
                ->andWhere(['type' => [1, 3]])
                ->orderBy('block_height desc')
                ->asArray()
                ->all();
            $result = [
                'node' => [
                    'count' => 0,
                    'height' => 0,
                    'list' => []
                ],
                'app' => [
                    'count' => 0,
                    'height' => 0,
                    'list' => []
                ],
            ];
            $hashs = [];
            foreach ($server as $val) {
                if (isset($hashs[$val['hash']])) {
                    $hashs[$val['hash']]++;
                } else {
                    $hashs[$val['hash']] = 1;
                }
            }
            //正统hash
            $hash = $server ? $server[0]['hash'] : '';
            if (count($hashs) > 1) {
                $c = max($hashs);
                $hash = array_search($c, $hashs);
            }
            //todo 正统高度应该是正统hash对应的高度
            $height = $server ? $server[0]['block_height'] : 0;
            $max_time = \Yii::$app->params['max_time_diff'] ?? 600;
            $max_height = \Yii::$app->params['max_height_diff'] ?? 10;

            $notice = $abnormal = 0;
            foreach ($server as &$item) {
                $item['inner_ip'] = long2ip($item['inner_ip']);
                $item['out_ip'] = long2ip($item['out_ip']);
                $updated_at = $item['updated_at'];
                $item['updated_at'] = date('Y-m-d H:i:s', $item['updated_at']);
                $item['desc'] = "";
                if (($diff = time() - $updated_at) > $max_time) {
                    $item['status'] = 'yellow';//异常
                    $item['desc'] = "服务器" . floor($diff / 60) . "min未更新";
                    $notice++;
                    continue;
                }
                if (($diff = time() - $item['block_time']) > $max_time) {
                    $item['status'] = 'red';
                    $item['desc'] = "节点" . ($diff / 60) . "min未更新";
                    $abnormal++;
                    continue;
                }
                if (($diff = $height - $item['block_height']) > $max_height) {
                    $item['status'] = 'red';
                    $item['desc'] = "落后{$diff}个区块";
                    $abnormal++;
                    continue;
                }
                if ($item['block_height'] == $height && $hash && $item['hash'] != $hash) {
                    $item['status'] = 'red';
                    $item['desc'] = "相同高度不同hash";
                    $abnormal++;
                    continue;
                }
                if (($diff = $height - $item['block_height']) > 0) {
                    $item['status'] = 'yellow';//异常
                    $item['desc'] = "落后{$diff}个区块";
                    $notice++;
                    continue;
                }
                $item['status'] = 'green';//正常
            }

            $result['node']['list'] = $server;
            $result['node']['count'] = count($server);
            $result['node']['height'] = $height;
            $result['node']['notice'] = $notice;
            $result['node']['abnormal'] = $abnormal;
            return ['code' => 200, 'data' => $result];
        }
        return $this->handleError();
    }

    public function getServerList($params)
    {
        $this->setScenario(self::SCENARIO_GET_SERVER_LIST);
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $one = self::findOne(['app_key' => $this->app_key, 'status' => 1]);
            if (!$one)
                return ['code' => 202, 'error' => 'data not fund', 'message' => '数据未找到'];
            $server = Server::find()->select('id,name,type,inner_ip,out_ip,port')->where(['id' => $one->server_ids])->asArray()->all();
            $result = ['app_name' => $one->name, 'list' => [], 'app_list' => []];
            foreach ($server as $item) {
                if ($item['type'] == Server::TYPE_NODE) {
                    $result['list'][] = [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'inner_ip' => long2ip($item['inner_ip']),
                        'out_ip' => long2ip($item['out_ip']),
                        'port' => $item['port']
                    ];
                } elseif ($item['type'] == Server::TYPE_APP) {
                    $result['app_list'][] = [
                        'id' => $item['id'],
                        'inner_ip' => long2ip($item['inner_ip']),
                        'out_ip' => long2ip($item['out_ip']),
                        'port' => $item['port'],
                        'url' => long2ip($item['out_ip']) . ':' . $item['port'],
                    ];
                } else {
                    $result['app_list'][] = [
                        'id' => $item['id'],
                        'inner_ip' => long2ip($item['inner_ip']),
                        'out_ip' => long2ip($item['out_ip']),
                        'port' => $item['port'],
                        'url' => long2ip($item['out_ip']) . ':' . $item['port'],
                    ];
                    $result['list'][] = [
                        'id' => $item['id'],
                        'name' => $item['name'],
                        'inner_ip' => long2ip($item['inner_ip']),
                        'out_ip' => long2ip($item['out_ip']),
                        'port' => $item['port']
                    ];
                }
            }

            return ['code' => 200, 'data' => $result];
        }
        return $this->handleError();
    }

    public function changeNotify($params)
    {
        $this->setScenario(self::SCENARIO_CHANGE_NOTIFY);
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $one = self::findOne(['app_key' => $this->app_key]);
            if (!$one) {
                return ['code' => 202, 'error' => 'data not fund', 'message' => '数据不存在'];
            }
            $manager = $one->manager;
            if (!isset($manager[$this->label])) {
                return ['code' => 202, 'error' => 'manager not fund', 'message' => '管理者不存在'];
            }
            $manager[$this->label]['notify_type'] = $this->notify_type;
            $one->manager = $manager;
            $one->save(false);
            return ['code' => 200, 'message' => 'ok'];
        }
        return $this->handleError();
    }

    public function changeMonitor($params)
    {
        $this->setScenario(self::SCENARIO_CHANGE_MONITOR);
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $one = self::findOne(['app_key' => $this->app_key]);
            if (!$one) {
                return ['code' => 202, 'error' => 'data not fund', 'message' => '数据不存在'];
            }
            $one->status = $this->status;
            $one->save(false);
            /*if ($one->status == self::STATUS_NOT_MONITOR) {
                Relation::deleteAll(['app_id' => $one->id]);
            } elseif ($one->status == self::STATUS_MONITOR) {
                $relation = [];
                foreach ($one->server_ids as $id) {
                    $relation[] = [$one->id, $this->name, $id, 0, 0, time(), time()];
                }
                $this->_addRelation($relation);
            }*/
            return ['code' => 200, 'message' => 'ok'];
        }
        return $this->handleError();
    }

    public function del($params)
    {
        $this->setScenario(self::SCENARIO_DEL);
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $one = self::findOne($this->id);
            if (!$one)
                return ['code' => 202, 'data' => [], 'message' => 'data not fund', 'error' => '数据不存在'];
            if ($one->status == self::STATUS_DEL)
                return ['code' => 202, 'data' => [], 'message' => 'data aleary delete', 'error' => '数据已删除'];
            Relation::deleteAll(['app_id' => $one->id]);
            $one->status = self::STATUS_DEL;
            $res = $one->save(false);
            return ['code' => 200, 'data' => [], 'message' => 'ok'];
        }
        return $this->handleError();
    }

    private function _addRelation($relation)
    {
        if ($relation) {
            $field = ['app_id', 'app_name', 'server_id','server_name', 'inner_ip', 'out_ip', 'created_at', 'updated_at'];
            \Yii::$app->getDb()->createCommand()->batchInsert(Relation::tableName(), $field, $relation)->execute();
        }
    }

    public function statics($params)
    {
        $this->load(['App' => $params]);
        if ($this->validate()) {
            $query = self::find()
                //->with(['node'])
                ->select('id,name,server_ids');
            if (!empty($this->search)) {
                if (preg_match_all('/[a-zA-Z0-9]+$/', $this->search, $r)) {
                    $query->where(['like', 'app_key', $this->search]);
                } else {
                    $query->where(['like', 'name', $this->search]);
                }
            }
            $provider = new ActiveDataProvider([
                'query' => $query->asArray(),
                'sort' => [
                    'defaultOrder' => [
                        'id' => SORT_ASC,
                    ]
                ],
                'pagination' => [
                    'pageSize' => $this->pageSize,
                    'page' => $this->page - 1
                ]
            ]);
            $models = $provider->getModels();
            array_walk($models, function (&$item) {
                $ids = json_decode($item['server_ids'], true);
                $res = Server::find()
                    ->where(['id' => $ids])
                    ->select(new Expression('count(*) as count,max(block_height) height'))
                    ->andWhere(['type' => [1, 3]])
                    ->asArray()
                    ->one();

                $item['count'] = $res['count'] ?? 0;
                $item['height'] = $res['height'] ?? 0;
                unset($item['server_ids']);
            });
            return ['code' => 200, 'data' => ['list' => $models, 'total' => $provider->getTotalCount()]];
        }
        return $this->handleError();
    }

    private function _rand($length = 16)
    {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $i = 0;
        $res = '';
        $len = strlen($str) - 1;
        while ($i++ <= $length) {
            $res .= $str[mt_rand(0, $len)];
        }
        return $res;
    }
}