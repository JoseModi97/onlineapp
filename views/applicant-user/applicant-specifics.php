<?php

use yii\helpers\Html;
// Removed: use yii\widgets\ActiveForm; // ActiveForm is now started in update-wizard.php
use yii\jui\DatePicker;

/** @var yii\web\View $this */
/** @var app\models\AppApplicant|null $appApplicantModel Nullable if not the active step for this model */
/** @var yii\widgets\ActiveForm $form Passed from update-wizard.php */
?>

<div class="applicant-specifics-form">

    <?php // ActiveForm::begin() is removed. The form is started in update-wizard.php ?>

    <?php if ($appApplicantModel): // Check if model is provided for this step ?>
        <?= $form->field($appApplicantModel, 'gender')->dropDownList(['Male' => 'Male', 'Female' => 'Female'], ['prompt' => 'Select Gender']) ?>
        <?= $form->field($appApplicantModel, 'dob')->widget(DatePicker::class, [
            'options' => ['class' => 'form-control'],
            'clientOptions' => ['dateFormat' => 'yy-mm-dd', 'changeYear' => true, 'changeMonth' => true, 'yearRange' => '-100:+0'],
        ]) ?>
        <?= $form->field($appApplicantModel, 'religion')->textInput(['maxlength' => true]) ?>
        <?= $form->field($appApplicantModel, 'country_code')->textInput(['maxlength' => true]) ?>
        <?= $form->field($appApplicantModel, 'national_id')->textInput(['maxlength' => true]) ?>
        <?= $form->field($appApplicantModel, 'marital_status')->dropDownList([
            'Single' => 'Single',
            'Married' => 'Married',
            'Divorced' => 'Divorced',
            'Widowed' => 'Widowed',
        ], ['prompt' => 'Select Marital Status']) ?>
    <?php else: ?>
        <p>Applicant specifics form is not available at this step.</p>
    <?php endif; ?>

    <?php // Submit buttons are removed. They are now in update-wizard.php ?>

    <?php // ActiveForm::end() is removed. The form is ended in update-wizard.php ?>

</div>