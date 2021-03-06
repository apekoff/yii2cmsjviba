<?php

return [
    [
        'class' => 'backend\modules\articles\components\ArticleUrlRule',
        'pattern' => '<category:[\w-]+>/<name:[\w-]+>',
        'route' => 'articles/article/view',
        'template' => '{category}/{sefPart}',
        'cacheComponentName' => 'memcache'
    ],
    [
        'class' => 'backend\modules\articles\components\CategoryUrlRule',
        'pattern' => '<category:[\w-]+>',
        'route' => 'articles/category/view',
        'template' => '{sefPart}',
        'cacheComponentName' => 'memcache'
    ],
    [
        'class' => 'backend\modules\pages\components\PageUrlRule',
        'pattern' => 'pages/<name:[\w-]+>',
        'route' => 'pages/page/view',
        'template' => 'pages/{sefPart}',
        'cacheComponentName' => 'memcache'
    ],
];