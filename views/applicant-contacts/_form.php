<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Added
use yii\helpers\Url; // Added

/** @var yii\web\View $this */
/** @var app\models\AppApplicantContacts $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-contacts-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'contact_id')->textInput() ?>

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

    <?= $form->field($model, 'contact_type_id')->widget(Select2::classname(), [
        'initValueText' => ($model->contact_type_id && $model->contactType) ? $model->contactType->contact_type_name : '',
        'options' => ['placeholder' => 'Search for a contact type ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 1,
            'ajax' => [
                'url' => Url::to(['contact-types/contact-type-list']),
                'dataType' => 'json',
                'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
            'templateResult' => new \yii\web\JsExpression('function(item) { return item.text; }'),
            'templateSelection' => new \yii\web\JsExpression('function(item) { return item.text; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'full_names')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'calling_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email_address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'postal_address')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'postal_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'town')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'country_code')->textInput() ?>

    <?= $form->field($model, 'primary_contact')->textInput() ?>

    <?= $form->field($model, 'relationship')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
