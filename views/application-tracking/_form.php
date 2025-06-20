<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Added
use yii\helpers\Url; // Added

/** @var yii\web\View $this */
/** @var app\models\AppApplicationTracking $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-tracking-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'application_id')->textInput() ?>

    <?= $form->field($model, 'status_id')->widget(Select2::classname(), [
        'initValueText' => ($model->status_id && $model->status) ? $model->status->status_name : '',
        'options' => ['placeholder' => 'Search for a status ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 1,
            'ajax' => [
                'url' => Url::to(['admission-status/admission-status-list']),
                'dataType' => 'json',
                'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
            'templateResult' => new \yii\web\JsExpression('function(item) { return item.text; }'),
            'templateSelection' => new \yii\web\JsExpression('function(item) { return item.text; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'remarks')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'audit_date')->textInput() ?>

    <?= $form->field($model, 'user_id')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
