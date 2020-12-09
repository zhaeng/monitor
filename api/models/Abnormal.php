<?php


namespace api\models;


use common\models\ServerAbnormal;
use yii\data\ActiveDataProvider;

class Abnormal extends ServerAbnormal
{
    const SCENARIO_DEAL = 'deal';
    public $search;

    public function rules()
    {
        return [
            [['search', 'page', 'pageSize'], 'safe'],
            ['id', 'required', 'on' => self::SCENARIO_DEAL]
        ];
    }

    public function search($params)
    {
        $this->load(['Abnormal' => $params]);
        if ($this->validate()) {
            $query = self::find()
                ->select('id,app_id,app_key,app_name,server_id,inner_ip,out_ip,status,server_name,remark,created_at,updated_at')
                ->where(['status' => 0])
                ->filterWhere(['app_id' => $this->search])
                ->OrFilterwhere(['server_id' => $this->search])
                ->OrFilterwhere(['id' => $this->search])
                ->asArray();
            $provider = new ActiveDataProvider([
                'query' => $query,
                'sort' => [
                    'defaultOrder' => [
                        'id' => SORT_DESC,
                    ]
                ],
                'pagination' => [
                    'pageSize' => $this->pageSize,
                    'page' => $this->page - 1
                ]
            ]);
            $models = $provider->getModels();
            array_walk($models, function (&$item) {
                $item['inner_ip'] = long2ip($item['inner_ip']);
                $item['out_ip'] = long2ip($item['out_ip']);
                $item['created_at'] = date('Y-m-d H:i:s', $item['created_at']);
                $item['updated_at'] = date('Y-m-d H:i:s', $item['updated_at']);
            });
            return ['code' => 200, 'data' => ['list' => $models, 'total' => $provider->getTotalCount()]];
        }
        return $this->handleError();
    }

    public function deal($params)
    {
        $this->setScenario(self::SCENARIO_DEAL);
        $this->load(['Abnormal' => $params]);
        if ($this->validate()) {
            $one = self::findOne($this->id);
            if (!$one)
                return ['code' => 202, 'error' => 'data not fund', 'message' => '数据未找到'];
            if ($one->status == 1)
                return ['code' => 202, 'error' => 'data not fund', 'message' => '数据已处理'];
            $one->status = 1;
            $one->save(false);

            return ['code' => 200];
        }
        return $this->handleError();
    }
}