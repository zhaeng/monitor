<?php


namespace api\models;


use common\models\Relation;
use yii\data\ActiveDataProvider;

class Server extends \common\models\Server
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_CHANGE = 'change';
    const SCENARIO_DEL = 'del';
    const SCENARIO_UPDATE_INFO = 'update-info';
    const SCENARIO_UPDATE_DATA = 'update-data';


    public $search;

    public $os;
    public $cpu;
    public $ip;//内网ip
    public $memory;
    public $disk;
    public $app_key;
    public $list;

    public function rules()
    {
        return [
            [['page','pageSize','search'],'safe'],
            [['name','inner_ip','out_ip','type','company','remark','port'],'required','on' => [self::SCENARIO_ADD,self::SCENARIO_CHANGE]],
            ['id','required','on' => [self::SCENARIO_CHANGE,self::SCENARIO_DEL]],
            ['status','default','value' => 1],
            [['inner_ip','out_ip'],'ip','on' => [self::SCENARIO_ADD,self::SCENARIO_CHANGE]],
            [['os','cpu','ip','memory','disk','app_key'],'required','on' => self::SCENARIO_UPDATE_INFO],
            [['list','app_key'],'required','on' => self::SCENARIO_UPDATE_DATA]
        ];
    }

    public function getAppName()
    {
        return $this->hasMany(Relation::class,['server_id' => 'id'])->select('app_id,server_id')->with(['name']);
    }


    public function addServer($params)
    {
        $this->setScenario(self::SCENARIO_ADD);
        $this->load(['Server' => $params]);
        if ($this->validate()){
            $this->uid = \Yii::$app->getUser()->id;
            $this->inner_ip = ip2long($this->inner_ip);
            $this->out_ip = ip2long($this->out_ip);
            $this->config_info = [];
            $res = $this->save(false);
            return ['code' => 200,'data' => [],'message' => 'ok'];
        }
        return $this->handleError();
    }

    public function updateServer($params)
    {
        $this->setScenario(self::SCENARIO_CHANGE);
        $this->load(['Server' => $params]);
        if ($this->validate()){
            $one = self::findOne($this->id);
            if (!$one)
                return ['code' => 202,'data' => [],'message' => 'data not fund','error' => '数据不存在'];
            $one->inner_ip = ip2long($this->inner_ip);
            $one->out_ip = ip2long($this->out_ip);
            $one->name = $this->name;
            $one->type = $this->type;
            $one->company = $this->company;
            $one->remark = $this->remark;
            $res = $one->save(false);
            return ['code' => 200,'data' => [],'message' => 'ok'];
        }
        return $this->handleError();
    }

    public function del($params)
    {
        $this->setScenario(self::SCENARIO_DEL);
        $this->load(['Server' => $params]);
        if ($this->validate()) {
            $one = self::findOne($this->id);
            if (!$one)
                return ['code' => 202,'data' => [],'message' => 'data not fund','error' => '数据不存在'];
            if ($one->status == 0)
                return ['code' => 202,'data' => [],'message' => 'data aleary delete','error' => '数据已删除'];
            $c = Relation::find()->select('app_name')->where(['server_id' => $this->id])->column();
            if ($c) {
                $msg = '当前服务器有' . count($c) . '个项目['.implode(',',$c).']正在使用，不能删除';
                return ['code' => 202,'data' => [],'message' => $msg,'error' => $msg];
            }
            $one->status = 0;
            $res = $one->save(false);
            return ['code' => 200,'data' => [],'message' => 'ok'];
        }
        return $this->handleError();
    }

    public function search($params)
    {
        $this->load(['Server' => $params]);
        if ($this->validate()) {
            $query = self::find()
                ->with(['appName'])
                ->where(['status' => 1])
                ->select('id,name,inner_ip,out_ip,port,type,company,remark,config_info,created_at,updated_at');
            if (!empty($this->search)) {
                if(is_numeric($this->search)){
                    $query->where(['id' => (int)$this->search]);
                } elseif ($r = ip2long($this->search)) {
                    $query->andWhere(['inner_ip' => $r])->orWhere(['out_ip' => $r]);
                } else {
                    $query->andWhere(['like','name',$this->search]);
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
            array_walk($models,function (&$item){
                $item['config_info'] = json_decode($item['config_info'],true);
                $item['inner_ip'] = long2ip($item['inner_ip']);
                $item['out_ip'] = long2ip($item['out_ip']);
                $item['created_at'] = date('Y-m-d H:i:s',$item['created_at']);
                $item['updated_at'] = date('Y-m-d H:i:s',$item['updated_at']);
                array_walk($item['appName'],function ($val) use (&$item){
                    $item['app'][] = $val['name']['name'];
                });
                unset($item['appName']);
            });
            return ['code' => 200,'data' => ['list' => $models,'total' => $provider->getTotalCount()]];
        }
        return $this->handleError();
    }

    public function enableList($params)
    {
        $this->load(['Server' => $params]);
        if ($this->validate()) {
            $query = self::find()
                ->where(['is_used' => 0])
                ->select('id,name,inner_ip,out_ip,port,type');

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
            array_walk($models,function (&$item){
                $item['inner_ip'] = long2ip($item['inner_ip']);
                $item['out_ip'] = long2ip($item['out_ip']);
            });
            return ['code' => 200,'data' => ['list' => $models,'total' => $provider->getTotalCount()]];
        }
        return $this->handleError();
    }

    public function updateServerInfo($params)
    {
        $this->setScenario(self::SCENARIO_UPDATE_INFO);
        $this->load(['Server' => $params]);
        if ($this->validate()) {
            $app = App::findOne(['app_key' => $this->app_key,'status' => App::STATUS_MONITOR]);
            if (!$app)
                return ['code' => 202,'error' => 'app not fund','message' => '应用未找到'];
            $servers = self::find()->where(['id' => $app->server_ids,'status' => 1])->all();
            $ip = ip2long($this->ip);
            $flag = false;
            /**
             * @var self $item
             */
            foreach ($servers as $item) {
                if($item->inner_ip == $ip) {
                    $item->config_info = [
                        'os' => $this->os,
                        'cpu' => $this->cpu,
                        'memory' => $this->memory,
                        'disk' => $this->disk
                    ];
                    $item->save(false);
                    $flag = true;
                }
            }

            if($flag) {
                return ['code' => 200];
            }
            return ['code' => 202,'error' => 'server not fund','message' => '服务器未找到'];

        }
        return $this->handleError();
    }

    public function updateData($params)
    {
        $this->setScenario(self::SCENARIO_UPDATE_DATA);
        $this->load(['Server' => $params]);
        if ($this->validate()) {
            $app = App::findOne(['app_key' => $this->app_key,'status' => App::STATUS_MONITOR]);
            if (!$app)
                return ['code' => 202,'error' => 'app not fund','message' => '应用未找到'];
            $node = [];
            foreach ($this->list as $val) {
                $key = substr($val['node'],strrpos($val['node'],'/') + 1);
                $node[$key] = [
                    'height' => $val['height'] ?? 0,
                    'sync' => (int)$val['sync'] ?? 0,
                    'blocktime' => $val['blocktime'] ?? 0,
                    'hash' => $val['hash'] ?? '',
                ];
            }
            $servers = self::find()->where(['id' => $app->server_ids,'type' => [1,3],'status' => 1])->all();
            /**
             * @var self $item
             */
            foreach ($servers as $item){
                $k = long2ip($item->out_ip) . ':' . $item['port'];
                if (isset($node[$k])) {
                    $item->block_height = $node[$k]['height'];
                    $item->sync = $node[$k]['sync'];
                    $item->block_time = $node[$k]['blocktime'];
                    $item->hash = $node[$k]['hash'];
                    $item->updated_at = time();
                    $item->save(false);
                }
            }
            return ['code' => 200];
        }
        return $this->handleError();
    }
}