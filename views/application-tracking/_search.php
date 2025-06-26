<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationTrackingSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-tracking-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'tracking_id') ?>

    <?= $form->field($model, 'application_id') ?>

    <?= $form->field($model, 'status_id') ?>

    <?= $form->field($model, 'remarks') ?>

    <?= $form->field($model, 'audit_date') ?>

    <?php // echo $form->field($model, 'user_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
