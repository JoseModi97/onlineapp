<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationIntake $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-intake-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'intake_code')->textInput() ?>

    <?= $form->field($model, 'intake_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'academic_year')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'degree_code')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'application_deadline')->textInput() ?>

    <?= $form->field($model, 'reporting_date')->textInput() ?>

    <?= $form->field($model, 'start_date')->textInput() ?>

    <?= $form->field($model, 'end_date')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
