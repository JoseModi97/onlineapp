<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationIntakeSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-intake-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'intake_code') ?>

    <?= $form->field($model, 'intake_name') ?>

    <?= $form->field($model, 'academic_year') ?>

    <?= $form->field($model, 'degree_code') ?>

    <?= $form->field($model, 'application_deadline') ?>

    <?php // echo $form->field($model, 'reporting_date') ?>

    <?php // echo $form->field($model, 'start_date') ?>

    <?php // echo $form->field($model, 'end_date') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
