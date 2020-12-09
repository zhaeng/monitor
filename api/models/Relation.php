<?php


namespace api\models;


class Relation extends \common\models\Relation
{
    const SCENARIO_SERVER_APP = 'server-app';

    public function rules()
    {
        return [
            ['server_id','required','on' => self::SCENARIO_SERVER_APP]
        ];
    }

    public function relation($params)
    {
        $this->setScenario(self::SCENARIO_SERVER_APP);
        $this->load(['Relation' => $params]);
        if ($this->validate()) {
            $res = self::find()->select('app_id,app_name')->where(['server_id' => $this->server_id])->asArray()->all();
            $is_use = false;
            $app_name = '';
            if ($res) {
                $is_use = true;
                $app_name = implode(',',array_column($res,'app_name'));
            }
            return ['data' => ['is_use' => $is_use,'app_name' => $app_name]];
        }
        return $this->handleError();
    }
}