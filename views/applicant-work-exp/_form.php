<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2; // Added
use yii\helpers\Url; // Added

/** @var yii\web\View $this */
/** @var app\models\AppApplicantWorkExp $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-work-exp-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'experience_id')->textInput() ?>

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

    <?= $form->field($model, 'employer_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'designation')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'year_from')->textInput() ?>

    <?= $form->field($model, 'year_to')->textInput() ?>

    <?= $form->field($model, 'assignment')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'relevant')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
