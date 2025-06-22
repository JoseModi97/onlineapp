<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */
/** @var app\models\AppApplicant $appApplicantModel */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'surname')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'other_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'email_address')->textInput(['maxlength' => true]) ?>
    <?php // $form->field($model, 'country_code')->textInput(['maxlength' => true]) // This country_code is from AppApplicantUser, we'll use the one from AppApplicant ?>
    <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true]) ?>

    <hr/>
    <h3>Applicant Details</h3>

    <?= $form->field($appApplicantModel, 'gender')->dropDownList(['Male' => 'Male', 'Female' => 'Female'], ['prompt' => 'Select Gender']) ?>
    <?= $form->field($appApplicantModel, 'dob')->widget(\yii\jui\DatePicker::class, [
        'options' => ['class' => 'form-control'],
        'dateFormat' => 'php:Y-m-d',
    ]) ?>
    <?= $form->field($appApplicantModel, 'religion')->textInput(['maxlength' => true]) ?>
    <?= $form->field($appApplicantModel, 'country_code')->textInput(['type' => 'number']) // Assuming this is the ID of a country, adjust if it's a string code ?>
    <?= $form->field($appApplicantModel, 'national_id')->textInput(['type' => 'number']) ?>
    <?= $form->field($appApplicantModel, 'marital_status')->dropDownList([
        'Single' => 'Single',
        'Married' => 'Married',
        'Divorced' => 'Divorced',
        'Widowed' => 'Widowed',
    ], ['prompt' => 'Select Marital Status']) ?>


    <hr/>
    <h3>Account Details</h3>
    <?php if ($model->isNewRecord || !empty($model->password) || !empty($model->activation_code)): // Only show password fields if relevant ?>
        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
    <?php endif; ?>
    <?php // Fields like activation_code, salt, status, reg_token are usually system-managed and not directly edited by users in a wizard like this. ?>
    <?php // Consider removing them or making them read-only if displayed. For now, I'll comment them out. ?>
    <?php // $form->field($model, 'activation_code')->textInput(['maxlength' => true]) ?>
    <?php // $form->field($model, 'salt')->textInput(['maxlength' => true]) ?>
    <?php // $form->field($model, 'status')->textInput(['maxlength' => true]) ?>
    <?php // $form->field($model, 'date_registered')->textInput() ?>
    <?php // $form->field($model, 'reg_token')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'profile_image')->textInput(['maxlength' => true]) // Consider using a file input widget here ?>
    <?= $form->field($model, 'username')->textInput() ?>
    <?= $form->field($model, 'change_pass')->checkbox() ?>


    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>