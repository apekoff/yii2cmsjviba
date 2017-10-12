<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\articles\models\Article */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Articles', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="article-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'article_category_ids',
            'user_id',
            'object_id',
            'name',
            'status',
            'created_at',
            'updated_at',
            'published_at',
            [
                'label' => Yii::t('app', 'Photo'),
                'format' => 'raw',
                'value' => Html::img(Yii::$app->photoManager->getPhotoUrl($model, 'photo', 'small'))
            ]
        ],
    ]) ?>

</div>