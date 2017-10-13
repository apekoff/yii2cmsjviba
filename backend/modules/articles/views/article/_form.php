<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\base\Widget;
use yii\helpers\Url;
use yii\bootstrap\Tabs;
use backend\modules\articles\models\ArticleInfo;

/* @var $this yii\web\View */
/* @var $model app\modules\articles\models\ArticleForm */
/* @var $form yii\widgets\ActiveForm */
/* @var $langs array */
?>

<div class="article-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList($statuses) ?>

    <?= $form->field($model, 'photo')->widget(common\components\photoField\Widget::className(), [
        'id' => 'articlePhotoUploader',
        'uploadUrl' => Url::to(['/articles/article/upload-photo'])
    ]) ?>
    
    <?php
        $tabItems = [];
        foreach ($langs as $lang) {
            $infoModel = new ArticleInfo();
            $infoModel->attributes = $model->infos[$lang['name']];
            $tabItems[] = [
                'label' => $lang['label'],
                'content' => $this->render('_form_info', [
                    'lang' => $lang,
                    'info' => $infoModel,
                    'form' => $form,
                    'model' => $model
                ])
            ];
        }
    ?>
    
    <?= Tabs::widget([
        'items' => $tabItems
    ]) ?>
    
    <br><br><br>
    
    <div class="form-group">
        <?= Html::submitButton(
            $model->getScenario() == 'insert' ? 'Create' : 'Update',
            [
                'class' => $model->getScenario() == 'insert' ? 'btn btn-success' : 'btn btn-primary'
            ]
        ) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
