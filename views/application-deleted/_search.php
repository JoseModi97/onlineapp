<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationDeletedSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-deleted-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'application_id') ?>

    <?= $form->field($model, 'applicant_id') ?>

    <?= $form->field($model, 'intake_code') ?>

    <?= $form->field($model, 'study_center_code') ?>

    <?= $form->field($model, 'application_ref_no') ?>

    <?php // echo $form->field($model, 'application_date') ?>

    <?php // echo $form->field($model, 'offer_accepted') ?>

    <?php // echo $form->field($model, 'final_status') ?>

    <?php // echo $form->field($model, 'application_fee_id') ?>

    <?php // echo $form->field($model, 'payment_status') ?>

    <?php // echo $form->field($model, 'processing_date') ?>

    <?php // echo $form->field($model, 'phd_proposal') ?>

    <?php // echo $form->field($model, 'application_form') ?>

    <?php // echo $form->field($model, 'sync_status') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
