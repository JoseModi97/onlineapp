<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationPayments080420160909 $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-payments080420160909-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'application_id')->textInput() ?>

    <?= $form->field($model, 'transaction_id')->textInput() ?>

    <?= $form->field($model, 'receipt_no')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'amount_paid')->textInput() ?>

    <?= $form->field($model, 'currency_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'payment_channel')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'transaction_ref')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'payment_ref')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'processing_date')->textInput() ?>

    <?= $form->field($model, 'sync_status')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
