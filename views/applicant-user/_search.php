<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\AppApplicantUserSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-user-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'applicant_user_id') ?>

    <?= $form->field($model, 'surname') ?>

    <?= $form->field($model, 'other_name') ?>

    <?= $form->field($model, 'email_address') ?>

    <?= $form->field($model, 'country_code') ?>

    <?php // echo $form->field($model, 'mobile_no') ?>

    <?php // echo $form->field($model, 'password') ?>

    <?php // echo $form->field($model, 'activation_code') ?>

    <?php // echo $form->field($model, 'salt') ?>

    <?php // echo $form->field($model, 'status') ?>

    <?php // echo $form->field($model, 'date_registered') ?>

    <?php // echo $form->field($model, 'reg_token') ?>

    <?php // echo $form->field($model, 'profile_image') ?>

    <?php // echo $form->field($model, 'first_name') ?>

    <?php // echo $form->field($model, 'change_pass') ?>

    <?php // echo $form->field($model, 'username') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
