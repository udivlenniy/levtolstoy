<?php

// uncomment the following to define a path alias
// Yii::setPathOfAlias('local','path/to/local-folder');

// This is the main Web application configuration. Any writable
// CWebApplication properties can be configured here.
return array(
	'basePath'=>dirname(__FILE__).DIRECTORY_SEPARATOR.'..',
	'name'=>'Система менеджмента копирайтеров',
    'defaultController'=>'user',
	// preloading 'log' component
	'preload'=>array(
        'log',
        'bootstrap',
    ),
    // язык поумолчанию
    'sourceLanguage' => 'en_US',
    'language' => 'ru',
	// autoloading model and component classes
	'import'=>array(
        'application.models.*',
        'application.components.*',
        'application.helpers.*',
        'application.modules.user.models.*',
        'application.modules.user.components.*',
	),

	'modules'=>array(
        // модуль - CRUD операций для заданий копирайтору
        'project',

        'user'=>array(
            # encrypting method (php hash function)
            'hash' => 'md5',
            # send activation email
            'sendActivationMail' => false,
            # allow access for non-activated users
            'loginNotActiv' => false,
            # activate user on registration (only sendActivationMail = false)
            'activeAfterRegister' => false,
            # automatically login from registration
            'autoLogin' => true,
            # registration path
            'registrationUrl' => array('/user/registration'),
            # recovery password path
            'recoveryUrl' => array('/user/recovery'),
            # login form path
            'loginUrl' => array('/user/login'),
            # page after login
            'returnUrl' => array('/user/profile'),
            # page after logout
            'returnLogoutUrl' => array('/user/login'),
        ),
		'gii'=>array(
			'class'=>'system.gii.GiiModule',
			'password'=>'1',
			// If removed, Gii defaults to localhost only. Edit carefully to taste.
			'ipFilters'=>array('127.0.0.1','::1'),
		),
	),
	// application components
	'components'=>array(
        'user'=>array(
            // enable cookie-based authentication
            'class' => 'WebUser',
            'allowAutoLogin'=>true,
            'loginUrl' => array('/user/login'),
        ),
        'bootstrap'=>array(
            'class'=>'ext.bootstrap.components.Bootstrap', // assuming you extracted bootstrap under extensions
            'responsiveCss' => true,
        ),
		// uncomment the following to enable URLs in path-format
//        'cache'=>array(
//            'class'=>'system.caching.CFileCache',
//        ),
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
                '' => 'site/index',
                //'<controller:\w+>/'=>'<controller>/index',
                //'<module:\w+>/<controller:\w+>/'=>'<module>/<controller>/index',
                // стандартное правило для обработки 'post/update' и др.
                //'<controller:\w+>/<action:\w+>' => '<controller>/<action>',

				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
            'showScriptName'=>false,
		),

		// uncomment the following to use a MySQL database
		'db'=>array(
			'connectionString' => 'mysql:host=localhost;dbname=management',
			'emulatePrepare' => true,
			'username' => 'root',
			'password' => '',
			'charset' => 'utf8',
            'tablePrefix' => 'tbl_',
            // включаем профайлер
            'enableProfiling'=>true,
            // показываем значения параметров
            'enableParamLogging' => true,
            //'schemaCachingDuration'=>36000,
		),

		'errorHandler'=>array(
			// use 'site/error' action to display errors
			'errorAction'=>'site/error',
		),
        'log'=>array(
            'class'=>'CLogRouter',
            'routes'=>array(
                array(
                    'class'=>'CFileLogRoute',
                    'levels'=>'error, warning',
                ),
                  array(
                      // направляем результаты профайлинга в ProfileLogRoute (отображается
                      // внизу страницы)
                      'class'=>'CProfileLogRoute',
                      'levels'=>'profile',
                      'enabled'=>true,
                  ),
                  array(
                      'class' => 'CWebLogRoute',
                      'categories' => 'application',
                      'levels'=>'error, warning, trace, profile, info',
                  ),
                // uncomment the following to show log messages on web pages

//                array(
//                    'class'=>'CWebLogRoute',
//                ),

            ),
        ),
	),

	// application-level parameters that can be accessed
	// using Yii::app()->params['paramName']
	'params'=>array(
		// this is used in contact page
		'adminEmail'=>'webmaster@example.com',
        'perPage'=>50,
        // ID аттрибута TITLE в служебной таблице {{import_vars}}
        'title'=>3,
        // ID аттрибута - ключевые слова в таблице {{import_vars}}
        'key_words'=>1,
        // ID аттрибута - сведения, обработанные ключевики,в таблице {{import_vars}}
        'reduction'=>2,
        'h1'=>10,
        'h2'=>11,
        'h3'=> 12,
        'content1'=>7,
        'content2'=> 8,
        'content3'=> 9,
        'not_import'=>22,
        // если пустое значение, значит при проверках запускаем классы под каждую проверку, а не отправляем запросы к прогам
        'cheking_url'=>'',
	),
);