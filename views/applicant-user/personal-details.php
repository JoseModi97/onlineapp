<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */
/** @var app\models\AppApplicant $appApplicantModel Model for applicant specific details, now part of personal details step */
// $form variable is local to this view when ActiveForm::begin() is used here.

// Make sure $appApplicantModel is available. If not, initialize it.
// The controller should provide this, but as a fallback:
if (!isset($appApplicantModel)) {
    // This is a view context, direct model instantiation might not be ideal
    // but necessary if controller doesn't guarantee it.
    // Yii::warning('AppApplicant model was not provided to personal-details view, initializing new.');
    // $appApplicantModel = new \app\models\AppApplicant();
    // It's better to rely on the controller to pass $appApplicantModel.
    // The controller's loadModelsForStep should handle this.
}

?>

<div class="personal-details-form">

    <?php $form = ActiveForm::begin([
        'id' => 'personal-details-form',
        // Action will be handled by the main update-wizard URL, parameters define step
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?php
            $surnameOptions = ['maxlength' => true];
            if (!empty($model->surname)) {
                $surnameOptions['disabled'] = true;
            }
            ?>
            <?= $form->field($model, 'surname', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>{error}{hint}'
            ])->textInput($surnameOptions) ?>
            <?php if (!empty($model->surname)): ?>
                <?= Html::activeHiddenInput($model, 'surname') ?>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <?php
            $firstNameOptions = ['maxlength' => true];
            if (!empty($model->first_name)) {
                $firstNameOptions['disabled'] = true;
            }
            ?>
            <?= $form->field($model, 'first_name', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>{error}{hint}'
            ])->textInput($firstNameOptions) ?>
            <?php if (!empty($model->first_name)): ?>
                <?= Html::activeHiddenInput($model, 'first_name') ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'other_name', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user-tag"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email_address', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'mobile_no', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <hr/>
    <h4>Additional Details</h4>

    <?php
    // Fields from the former applicant-specifics.php, now using $appApplicantModel
    // Ensure $appApplicantModel is passed to this view by the controller.
    ?>

    <?= $form->errorSummary($appApplicantModel); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($appApplicantModel, 'gender', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-venus-mars"></i></span>{input}</div>{error}{hint}'
            ])->dropDownList(['Male' => 'Male', 'Female' => 'Female'], ['prompt' => 'Select Gender']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($appApplicantModel, 'dob', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>{input}</div>{error}{hint}'
            ])->widget(\yii\jui\DatePicker::class, [
                'model' => $appApplicantModel, // Ensure model is passed to widget if needed for model attribute context
                'attribute' => 'dob',
                'options' => ['class' => 'form-control'],
                'dateFormat' => 'yyyy-MM-dd', // Display format
                'clientOptions' => [
                    'dateFormat' => 'yy-mm-dd', // Format sent to server / used by jQuery UI
                    'changeYear' => true,
                    'changeMonth' => true,
                    'yearRange' => '-100:+0', // Example: 100 years in past up to current year
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
            ])->textInput(['maxlength' => true]) // Consider dropdown for actual countries
            ?>
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

    <?php // Navigation buttons are handled by the main update-wizard.php view and AJAX JS ?>
    <div class="form-group visually-hidden">
        <?= Html::submitButton('Submit', ['style' => 'display:none;']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
