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

    <?= $form->errorSummary($appApplicantModel); // Added error summary 
    ?>

    <?= $form->field($appApplicantModel, 'gender')->dropDownList(['Male' => 'Male', 'Female' => 'Female'], ['prompt' => 'Select Gender']) ?>
    <?= $form->field($appApplicantModel, 'dob')->widget(DatePicker::class, [
        'options' => ['class' => 'form-control'],
        'dateFormat' => 'yyyy-MM-dd', // server-side format (for DB compatibility)
        'clientOptions' => [
            'dateFormat' => 'yy-mm-dd', // client-side JS format
            'changeYear' => true,
            'changeMonth' => true,
            'yearRange' => '-100:+0',
        ],
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

    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-arrow-left"></i> Previous', ['class' => 'btn btn-info', 'name' => 'wizard_previous', 'formnovalidate' => true]) ?>
        <?= Html::submitButton('Next <i class="fas fa-arrow-right"></i>', ['class' => 'btn btn-primary', 'name' => 'wizard_next']) ?>
        <?= Html::submitButton('<i class="fas fa-times"></i> Cancel', ['class' => 'btn btn-secondary', 'name' => 'wizard_cancel', 'formnovalidate' => true]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>