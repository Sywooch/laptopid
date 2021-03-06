<?php

$params = require(__DIR__ . '/params.php');

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
	//'defaultRoute' => 'page/index',
    'bootstrap' => ['log'],
	'language' => 'et',
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'YtpyPkErCAMkSSRCgo5Js7M_kKQSk-Lf',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
		'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => true,
            'enableStrictParsing' => false,
            'rules' => [
				'/' => 'product/index',
				'login' => 'site/login',
                'page/<id:\d+>' => 'site/page',
				'page/<id:\d+>/<pageName>' => 'site/page',
				'edit/page/<id:\d+>' => 'site/edit-page',
				'product-field/update/<id:\d+>' => 'product-field/update',
				'product-field/create/<id:\d+>' => 'product-field/create',
				'field/get-fields-by-type/<id:\d+>' => 'field/get-fields-by-type',
				
				'user/login' 		=> 'user/security/login',
				'user/register' 	=> 'user/registration/register',
				'user/resend' 		=> 'user/registration/resend',
				'user/confirm' 		=> 'user/registration/confirm',
				'user/logout' 		=> 'user/security/logout',
				'user/request' 		=> 'user/recovery/request',
				'user/reset' 		=> 'user/recovery/reset',
				'user/profile' 		=> 'user/settings/profile',
				'user/account' 		=> 'user/settings/account',
				'user/networks' 	=> 'user/settings/networks',
				'user/show' 		=> 'user/profile/show',
	
				
				'<controller:\w+>/<id:\d+>' => '<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>/<id:\d+>/<pageName>' => '<controller>/<action>',
				'<controller:\w+>/<action:\w+>' => '<controller>/<action>',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => require(__DIR__ . '/db.php'),
		'authManager' => [
						   'class' => 'yii\rbac\DbManager',
						   'defaultRoles' => ['guest'],
		 ],
		'cart' => [
			'class' => 'yii2mod\cart\Cart',
		],
		'comparison' => [
			'class' => 'comparison\comparison\Comparison',
		],
    ],
	'modules' => [
		'user' => [
			'class' => 'dektrium\user\Module',
			'enableUnconfirmedLogin' => true,
			'confirmWithin' => 21600,
			'cost' => 12,
			'admins' => ['admin', 'tanel', 'Caupo'],
		],
		 'rbac' => [
			'class' => 'dektrium\rbac\Module',
		],
	],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = 'yii\debug\Module';

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = 'yii\gii\Module';
}

return $config;
