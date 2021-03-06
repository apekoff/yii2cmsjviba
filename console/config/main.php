<?php
$params = array_merge(
    require(__DIR__ . '/../../common/config/params.php'),
    require(__DIR__ . '/../../common/config/params-local.php'),
    require(__DIR__ . '/params.php'),
    require(__DIR__ . '/params-local.php')
);

return [
    'id' => 'app-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'controllerNamespace' => 'console\controllers',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\console\controllers\FixtureController',
            'namespace' => 'common\fixtures',
        ],
        'migrate' => [
            'class' => 'dmstr\console\controllers\MigrateController'
        ],
        'recache' => [
            'class' => 'console\controllers\RecacheController',
            'sources' => [
                'backend\modules\articles\components\ArticleUrlRule' => [
                    'pattern' => '<category:[\w-]+>/<name:[\w-]+>',
                    'route' => 'articles/article/view',
                    'template' => '{category}/{sefPart}',
                    'cacheComponentName' => 'memcache'
                ],
                'backend\modules\articles\components\CategoryUrlRule' => [
                    'pattern' => '<category:[\w-]+>',
                    'route' => 'articles/category/view',
                    'template' => '{sefPart}',
                    'cacheComponentName' => 'memcache'
                ],
                'backend\modules\pages\components\PageUrlRule' => [
                    'pattern' => 'pages/<name:[\w-]+>',
                    'route' => 'pages/page/view',
                    'template' => 'pages/{sefPart}',
                    'cacheComponentName' => 'memcache'
                ],
            ],
        ],
        'daemon' => [
            'class' => 'inpassor\daemon\Controller',
            'uid' => 'daemon', // The daemon UID. Giving daemons different UIDs makes possible to run several daemons.
            'pidDir' => '@runtime/daemon', // PID file directory.
            'logsDir' => '@runtime/logs', // Log files directory.
            'clearLogs' => false, // Clear log files on start.
            'workersMap' => [
                'watcher' => [
                    'class' => 'inpassor\daemon\workers\Watcher',
                    'active' => true, // If set to false, worker is disabled.
                    'maxProcesses' => 1, // The number of maximum processes of the daemon worker running at once.
                    'delay' => 60, // The time, in seconds, the timer should delay in between executions of the daemon worker.
                ],
                'urlRebuild' => 'console\controllers\workers\UrlRebuildWorker'
            ],
        ],
    ],
    'components' => [
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'modelFactory' => [
            'class' => 'common\components\ModelFactory'
        ],
        'urlManager' => [
            'class' => 'common\components\UrlManager',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => false,
            'rules' => require __DIR__ . '/../../backend/config/routes.php'
        ],
        'authManager' => [
            'class' => 'yii\rbac\PhpManager',
            'defaultRoles' => ['guest'],
            'itemFile' => '@common/rbac/items.php',
            'assignmentFile' => '@common/rbac/assignments.php',
            'ruleFile' => '@common/rbac/rules.php'
        ],
        'cacheAdapterFactory' => [
            'class' => 'common\components\caching\CacheAdapterFactory',
            'cacheComponentName' => 'memcache'
        ],
        'memcache' => [
            'class' => '\yii\caching\MemCache',
            'servers' => [
                [
                    'host' => '127.0.0.1',
                    'port' => 11211,
                    'weight' => 100,
                ],
            ]
        ]
    ],
    'params' => $params,
];
