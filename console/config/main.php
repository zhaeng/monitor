<?php
$params = array_merge(
    require (__DIR__ . '/../../common/config/params.php'),
    require (__DIR__ . '/../../common/config/params-local.php'),
    require (__DIR__ . '/params.php'),
    require (__DIR__ . '/params-local.php')
);

return [
    'id'                  => 'app-console',
    'basePath'            => dirname(__DIR__),
    'bootstrap'           => ['log'],
    'controllerNamespace' => 'console\controllers',
    'controllerMap'       => [
        'fixture' => [
            'class'     => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
    ],
    'components'          => [
        'log' => [
            'targets' => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'sms' => [
            'class' => 'common\components\SmsServer',
            'sign_id' => 14,
            'country' => 86,
            'app_key' => 'jiankong',
            'app_secret' => 'Eut0O3HzsUQLNgsi',
            'url' => 'http://118.31.52.32:8860/send/sms2',
            'isBatch' => true
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            'viewPath'          => '@mail',
            'useFileTransport'  =>  false,
            'transport' => [
                'class'         => 'Swift_SmtpTransport',
                'host'          => 'smtp.exmail.qq.com',
                'username'      => 'xlog@licai.cn',
                'password'      => 'Fuzamei123@33',
                'port'          => '25',
                //'encryption'    => 'ssl',
            ],
            'messageConfig' => [
                'charset' => 'UTF-8',
                'from' => ['xlog@licai.cn' => '节点监控系统']
            ],
        ],
    ],

    'params' => $params,
];
