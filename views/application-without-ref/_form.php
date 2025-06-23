<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Added
use yii\helpers\Url; // Added

/** @var yii\web\View $this */
/** @var app\models\AppApplicationWithoutRef $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-without-ref-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'applicant_id')->widget(Select2::classname(), [
        'initValueText' => ($model->applicant_id && $model->applicant && $model->applicant->applicantUser) ? 'ID: ' . $model->applicant_id . ' - ' . $model->applicant->applicantUser->first_name . ' ' . $model->applicant->applicantUser->surname : '',
        'options' => ['placeholder' => 'Search for an applicant ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 1,
            'ajax' => [
                'url' => Url::to(['applicant/applicant-list']),
                'dataType' => 'json',
                'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
            'templateResult' => new \yii\web\JsExpression('function(item) { return item.text; }'),
            'templateSelection' => new \yii\web\JsExpression('function(item) { return item.text; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'intake_code')->textInput() ?>

    <?= $form->field($model, 'study_center_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'application_ref_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'application_date')->textInput() ?>

    <?= $form->field($model, 'offer_accepted')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'final_status')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'application_fee_id')->widget(Select2::classname(), [
        'initValueText' => ($model->application_fee_id && $model->applicationFee) ? $model->applicationFee->programme_type . ' - ' . $model->applicationFee->amount . ' ' . $model->applicationFee->currency : '',
        'options' => ['placeholder' => 'Search for an application fee ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 1,
            'ajax' => [
                'url' => Url::to(['application-fees/application-fees-list']),
                'dataType' => 'json',
                'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
            'templateResult' => new \yii\web\JsExpression('function(item) { return item.text; }'),
            'templateSelection' => new \yii\web\JsExpression('function(item) { return item.text; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'payment_status')->textInput() ?>

    <?= $form->field($model, 'processing_date')->textInput() ?>

    <?= $form->field($model, 'phd_proposal')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'application_form')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sync_status')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
