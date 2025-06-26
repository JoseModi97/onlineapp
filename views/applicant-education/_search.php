<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicantEducationSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-education-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'education_id') ?>

    <?= $form->field($model, 'applicant_id') ?>

    <?= $form->field($model, 'edu_system_code') ?>

    <?= $form->field($model, 'institution_name') ?>

    <?= $form->field($model, 'edu_ref_no') ?>

    <?php // echo $form->field($model, 'year_from') ?>

    <?php // echo $form->field($model, 'year_to') ?>

    <?php // echo $form->field($model, 'grade') ?>

    <?php // echo $form->field($model, 'grade_per_student') ?>

    <?php // echo $form->field($model, 'points_score') ?>

    <?php // echo $form->field($model, 'pi_gpa') ?>

    <?php // echo $form->field($model, 'relevant') ?>

    <?php // echo $form->field($model, 'remarks') ?>

    <?php // echo $form->field($model, 'name_as_per_cert') ?>

    <?php // echo $form->field($model, 'file_path') ?>

    <?php // echo $form->field($model, 'file_name') ?>

    <?php // echo $form->field($model, 'cert_source') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
