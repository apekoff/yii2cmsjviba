<?php
return [
    'admin' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'articleCreate',
            'articleUpdate',
            'articleDelete',
            'articleList',
            'articleView',
            'categoryCreate',
            'categoryUpdate',
            'categoryDelete',
            'categoryList',
            'categoryView',
            'pageCreate',
            'pageUpdate',
            'pageDelete',
            'pageList',
            'pageView',
            'userCreate',
            'userUpdate',
            'userDelete',
            'userList',
            'userView',
        ],
    ],
    'user' => [
        'type' => 1,
        'ruleName' => 'userRole',
        'children' => [
            'articleView',
            'categoryView',
            'pageView',
            'userView',
        ],
    ],
    'articleCreate' => [
        'type' => 2,
    ],
    'articleUpdate' => [
        'type' => 2,
    ],
    'articleDelete' => [
        'type' => 2,
    ],
    'articleList' => [
        'type' => 2,
    ],
    'articleView' => [
        'type' => 2,
    ],
    'pageCreate' => [
        'type' => 2,
    ],
    'pageUpdate' => [
        'type' => 2,
    ],
    'pageDelete' => [
        'type' => 2,
    ],
    'pageList' => [
        'type' => 2,
    ],
    'pageView' => [
        'type' => 2,
    ],
    'categoryCreate' => [
        'type' => 2,
    ],
    'categoryUpdate' => [
        'type' => 2,
    ],
    'categoryDelete' => [
        'type' => 2,
    ],
    'categoryList' => [
        'type' => 2,
    ],
    'categoryView' => [
        'type' => 2,
    ],
    'userCreate' => [
        'type' => 2,
    ],
    'userUpdate' => [
        'type' => 2,
    ],
    'userDelete' => [
        'type' => 2,
    ],
    'userList' => [
        'type' => 2,
    ],
    'userView' => [
        'type' => 2,
    ],
];
