<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Added
use yii\helpers\Url; // Added

/** @var yii\web\View $this */
/** @var app\models\AppApplicantEducation $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-education-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'education_id')->textInput() ?>

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

    <?= $form->field($model, 'edu_system_code')->widget(Select2::classname(), [
        'initValueText' => ($model->edu_system_code && $model->eduSystem) ? $model->eduSystem->edu_system_name : '',
        'options' => ['placeholder' => 'Search for an education system ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 1,
            'ajax' => [
                'url' => Url::to(['education-system/education-system-list']),
                'dataType' => 'json',
                'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
            'templateResult' => new \yii\web\JsExpression('function(item) { return item.text; }'),
            'templateSelection' => new \yii\web\JsExpression('function(item) { return item.text; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'institution_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'edu_ref_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year_from')->textInput() ?>

    <?= $form->field($model, 'year_to')->textInput() ?>

    <?= $form->field($model, 'grade')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'grade_per_student')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'points_score')->textInput() ?>

    <?= $form->field($model, 'pi_gpa')->textInput() ?>

    <?= $form->field($model, 'relevant')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'remarks')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'name_as_per_cert')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'file_path')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'file_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'cert_source')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
