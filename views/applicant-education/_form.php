<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantEducation $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-education-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'education_id')->textInput() ?>

    <?= $form->field($model, 'applicant_id')->textInput() ?>

    <?= $form->field($model, 'edu_system_code')->textInput() ?>

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
