<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationPayments080420160909Search $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-payments080420160909-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'payment_id') ?>

    <?= $form->field($model, 'application_id') ?>

    <?= $form->field($model, 'transaction_id') ?>

    <?= $form->field($model, 'receipt_no') ?>

    <?= $form->field($model, 'amount_paid') ?>

    <?php // echo $form->field($model, 'currency_code') ?>

    <?php // echo $form->field($model, 'payment_channel') ?>

    <?php // echo $form->field($model, 'transaction_ref') ?>

    <?php // echo $form->field($model, 'payment_ref') ?>

    <?php // echo $form->field($model, 'processing_date') ?>

    <?php // echo $form->field($model, 'sync_status') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
