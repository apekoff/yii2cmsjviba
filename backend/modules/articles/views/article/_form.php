<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\base\Widget;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\modules\articles\models\Article */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="article-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->dropDownList($statuses) ?>

    <?= $form->field($model, 'photo')->widget(common\components\photoField\Widget::className(), [
        'id' => 'articlePhotoUploader',
        'uploadUrl' => Url::to(['/articles/article/upload-photo'])
    ]) ?>
    
    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
