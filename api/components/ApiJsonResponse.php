<?php
namespace api\components;

use Yii;
use yii\helpers\Json;
use common\helpers\ExtendFunction;
use yii\web\JsonResponseFormatter;

/**
 * api json格式化组件
 * @author CGA
 */
class ApiJsonResponse extends JsonResponseFormatter
{
    /**
     * Formats response data in JSON format.
     * @param Response $response
     */
    protected function formatJson($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/json; charset=UTF-8');
        if ($response->data !== null) {
            $options = $this->encodeOptions;
            if ($this->prettyPrint) {
                $options |= JSON_PRETTY_PRINT;
            }
            $data = $this->formatDate($response);
            $response->content = Json::encode($data, $options);
        }
    }

    /**
     * Formats response data in JSONP format.
     * @param Response $response
     */
    protected function formatJsonp($response)
    {
        $response->getHeaders()->set('Content-Type', 'application/javascript; charset=UTF-8');
        if (is_array($response->data) && isset($response->data['data'], $response->data['callback'])) {
            $data = $this->formatDate($response);
            $response->content = sprintf('%s(%s);', $response->data['callback'], Json::htmlEncode($data));
        } elseif ($response->data !== null) {
            $response->content = '';
            Yii::warning("The 'jsonp' response requires that the data be an array consisting of both 'data' and 'callback' elements.", __METHOD__);
        }
    }

    /**
     * 自定义API返回json格式
     */
    protected function formatDate($response)
    {
        if ($response->isSuccessful || $response->statusCode == 200) {
            $data = [
                //'success' => true,
                'code'    => 200,
                'message' => isset($response->data['message']) ? htmlspecialchars(addslashes($response->data['message'])) : '操作成功',
                'data'    => isset($response->data['data']) ? ExtendFunction::_safeData($response->data['data']) : ['uid' => Yii::$app->user->id],
            ];
        } else {
            $data = [
                //'success' => false,
                'code'    => $response->data['code'] == 0 ? $response->statusCode : $response->data['code'],
                'message' => htmlspecialchars(addslashes($response->data['message'])),
                'data'    => [],
            ];
            if (YII_DEBUG) {
                $data['info'] = ExtendFunction::_safeData($response->data);
            }
        }
        $response->statusCode = 200;
        return $data;
    }
}
