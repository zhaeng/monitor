<?php
$params = array_merge(
    require (__DIR__ . '/../../common/config/params.php'),
    require (__DIR__ . '/../../common/config/params-local.php'),
    require (__DIR__ . '/params.php'),
    require (__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'app-api',
    'basePath'            => dirname(__DIR__),
    'controllerNamespace' => 'api\controllers',
    'bootstrap'           => ['log'],
    'modules'             => [
        /*'v1' => [
            'class' => 'api\modules\v1\Module',
        ],
        'v2' => [
            'class' => 'api\modules\v2\Module',
        ],*/
        'oauth2' => [
            'class' => 'filsh\yii2\oauth2server\Module',
            'tokenParamName' => 'accessToken',
            'tokenAccessLifetime' => 3600 * 24,
            'storageMap' => [
                'user_credentials' => 'common\models\User',
            ],
            'grantTypes' => [
                'user_credentials' => [
                    'class' => 'OAuth2\GrantType\UserCredentials',
                ],
                'refresh_token' => [
                    'class' => 'OAuth2\GrantType\RefreshToken',
                    'always_issue_new_refresh_token' => true
                ]
            ]
        ],
    ],
    'defaultRoute' => 'index/index',
    'components'          => [
        'request'      => [
            'csrfParam'            => '_csrf-api',
            'parsers'              => [
                'application/json' => 'yii\web\JsonParser',
                'text/json'        => 'yii\web\JsonParser',
            ],
            'enableCsrfValidation' => false,
        ],
        'response'     => [
            'format'     => yii\web\Response::FORMAT_JSON,
            'charset'    => 'UTF-8',
            /*'formatters' => [
                'json' => 'api\components\ApiJsonResponse',
            ],*/
        ],
        'user'         => [
            'identityClass'   => 'common\models\User',
            'enableAutoLogin' => true,
            'enableSession'   => false,
            'identityCookie'  => ['name' => '_identity-api', 'httpOnly' => true],
            'loginUrl'        => ['error/login'],
        ],
        'log'          => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets'    => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager'   => [
            'enablePrettyUrl' => true,
            'showScriptName'  => false,
            'rules'           => [
                'POST oauth2/<action:\w+>' => 'oauth2/rest/<action>',
            ],
        ],
        'errorHandler' => [
            'class' => 'api\components\Handler',
            'errorAction' => 'error/index',
        ],

        'cache' => [
            'class' => 'yii\redis\Cache',
        ],
    ],
    'as cors'      => [
        'class' => 'yii\filters\Cors',
        'cors'  => [
            'Origin'                           => ['*'],
            'Access-Control-Request-Headers'   => ['*'],
            'Access-Control-Allow-Credentials' => true,
            'Access-Control-Request-Method'    => ['GET', 'HEAD', 'POST'],
        ],
    ],
    'params'       => $params,
];
