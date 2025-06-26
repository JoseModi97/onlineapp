<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/** @var yii\web\View $this */
/** @var app\models\AppApplicant $appApplicantModel */
?>

<div class="applicant-specifics-form">

    <?php $form = ActiveForm::begin([
        'id' => 'applicant-specifics-form',
    ]); ?>

    <?= $form->errorSummary($appApplicantModel); // Added error summary ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($appApplicantModel, 'gender', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-venus-mars"></i></span>{input}</div>{error}{hint}'
            ])->dropDownList(['Male' => 'Male', 'Female' => 'Female'], ['prompt' => 'Select Gender']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($appApplicantModel, 'dob', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>{input}</div>{error}{hint}'
            ])->widget(DatePicker::class, [
                'options' => ['class' => 'form-control'],
                'dateFormat' => 'yyyy-MM-dd',
                'clientOptions' => [
                    'dateFormat' => 'yy-mm-dd',
                    'changeYear' => true,
                    'changeMonth' => true,
                    'yearRange' => '-100:+0',
                ],
            ]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($appApplicantModel, 'religion', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-place-of-worship"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($appApplicantModel, 'country_code', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-flag"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($appApplicantModel, 'national_id', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-id-card"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($appApplicantModel, 'marital_status', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-ring"></i></span>{input}</div>{error}{hint}'
            ])->dropDownList([
                'Single' => 'Single',
                'Married' => 'Married',
                'Divorced' => 'Divorced',
                'Widowed' => 'Widowed',
            ], ['prompt' => 'Select Marital Status']) ?>
        </div>
    </div>

    <?php // Navigation buttons are now handled by the main update-wizard.php view and AJAX JS ?>
    <div class="form-group visually-hidden">
        <?= Html::submitButton('Submit', ['style' => 'display:none;']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>