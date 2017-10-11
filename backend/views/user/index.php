<?php

use yii\helpers\Html;
use yii\grid\GridView;
use common\models\User;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'email:email',
            'username',
            [
                'attribute' => 'status',
                'label' => Yii::t('app', 'Status'),
                'value' => function($model) {
                    $statuses = User::getAvailableStatuses();
                    return empty($statuses[$model->status]) ? Yii::t('app', 'Undefined') : $statuses[$model->status];
                }
            ],
            [
                'attribute' => 'role',
                'label' => Yii::t('app', 'Role'),
                'value' => function($model) {
                    $roles = User::getAvailableRoles();
                    return empty($roles[$model->role]) ? Yii::t('app', 'Undefined') : $roles[$model->role];
                }
            ],
            'created_at',
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
