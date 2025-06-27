<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppContactTypes $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-contact-types-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'contact_type_name')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
