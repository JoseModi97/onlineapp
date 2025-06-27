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
            <?php
            $surnameOptions = ['maxlength' => true];
            if (!empty($model->surname)) {
                $surnameOptions['disabled'] = true;
            }
            ?>
            <?= $form->field($model, 'surname', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>{error}{hint}'
            ])->textInput($surnameOptions) ?>
            <?php if (!empty($model->surname)): ?>
                <?= Html::activeHiddenInput($model, 'surname') ?>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <?php
            $firstNameOptions = ['maxlength' => true];
            if (!empty($model->first_name)) {
                $firstNameOptions['disabled'] = true;
            }
            ?>
            <?= $form->field($model, 'first_name', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>{error}{hint}'
            ])->textInput($firstNameOptions) ?>
            <?php if (!empty($model->first_name)): ?>
                <?= Html::activeHiddenInput($model, 'first_name') ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'other_name', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user-tag"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email_address', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'mobile_no', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <?php // Navigation buttons are now handled by the main update-wizard.php view and AJAX JS ?>
    <?php // Ensuring form group for spacing if any other elements were to be added later.
        // If no other elements, this div could also be removed.
    ?>
    <div class="form-group visually-hidden">
        <?php /* Dummy button to ensure form tag is not empty if no other buttons/elements are present, not strictly necessary with fields above */ ?>
        <?= Html::submitButton('Submit', ['style' => 'display:none;']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
