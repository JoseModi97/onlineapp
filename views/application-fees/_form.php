<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationFees $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-fees-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'application_fee_id')->textInput() ?>

    <?= $form->field($model, 'programme_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'amount')->textInput() ?>

    <?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <?= $form->field($model, 'date_added')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
