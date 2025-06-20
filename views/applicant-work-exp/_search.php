<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicantWorkExpSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-work-exp-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'experience_id') ?>

    <?= $form->field($model, 'applicant_id') ?>

    <?= $form->field($model, 'employer_name') ?>

    <?= $form->field($model, 'designation') ?>

    <?= $form->field($model, 'year_from') ?>

    <?php // echo $form->field($model, 'year_to') ?>

    <?php // echo $form->field($model, 'assignment') ?>

    <?php // echo $form->field($model, 'relevant') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
