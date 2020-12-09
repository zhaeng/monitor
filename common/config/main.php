<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'language'   => 'zh-CN',
    'timeZone'   => 'PRC',
    'charset'    => 'utf-8',
    'aliases' => [
        '@mail' => dirname(__DIR__) . '/mail',
    ],
    'components' => [
        'cache'     => [
            'class' => 'yii\redis\Cache',
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
    ]
];
