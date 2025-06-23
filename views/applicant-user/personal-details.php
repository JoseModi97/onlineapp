<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */
// $form variable is local to this view when ActiveForm::begin() is used here.
?>

<div class="personal-details-form">

    <?php $form = ActiveForm::begin([
        'id' => 'personal-details-form',
        // Action will be handled by the main update-wizard URL, parameters define step
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'surname', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>',
            ])->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'first_name', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>',
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'other_name', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>',
            ])->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email_address', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span>{input}</div>',
            ])->textInput(['maxlength' => true, 'type' => 'email']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'mobile_no', [
                'inputTemplate' => '<div class="input-group"><span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>{input}</div>',
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?php // Navigation buttons are now handled by the main update-wizard.php view and AJAX JS ?>
    <div class="form-group visually-hidden">
        <?= Html::submitButton('Submit', ['style' => 'display:none;']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
