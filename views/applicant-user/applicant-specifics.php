<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\jui\DatePicker;

/** @var yii\web\View $this */
/** @var beastbytes\wizard\WizardEvent $event */
/** @var app\models\AppApplicant $appApplicantModel */

$appApplicantModel = $event->data['appApplicantModel'];
?>

<div class="applicant-specifics-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($appApplicantModel, 'gender')->dropDownList(['Male' => 'Male', 'Female' => 'Female'], ['prompt' => 'Select Gender']) ?>
    <?= $form->field($appApplicantModel, 'dob')->widget(DatePicker::class, [
        'options' => ['class' => 'form-control'],
        'dateFormat' => 'php:Y-m-d',
    ]) ?>
    <?= $form->field($appApplicantModel, 'religion')->textInput(['maxlength' => true]) ?>
    <?= $form->field($appApplicantModel, 'country_code')->textInput(['type' => 'number']) ?>
    <?= $form->field($appApplicantModel, 'national_id')->textInput(['type' => 'number']) ?>
    <?= $form->field($appApplicantModel, 'marital_status')->dropDownList([
        'Single' => 'Single',
        'Married' => 'Married',
        'Divorced' => 'Divorced',
        'Widowed' => 'Widowed',
    ], ['prompt' => 'Select Marital Status']) ?>

    <div class="form-group">
        <?= Html::submitButton('Previous', ['class' => 'btn btn-default', 'name' => 'wizard_previous']) ?>
        <?= Html::submitButton('Next', ['class' => 'btn btn-primary', 'name' => 'wizard_next']) ?>
        <?= Html::submitButton('Cancel', ['class' => 'btn btn-default', 'name' => 'wizard_cancel']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
