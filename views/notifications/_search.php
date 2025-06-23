<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\NotificationsSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-notifications-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'notification_id') ?>

    <?= $form->field($model, 'applicant_id') ?>

    <?= $form->field($model, 'application_ref_no') ?>

    <?= $form->field($model, 'notification_type') ?>

    <?= $form->field($model, 'recipient') ?>

    <?php // echo $form->field($model, 'sender') ?>

    <?php // echo $form->field($model, 'subject') ?>

    <?php // echo $form->field($model, 'message') ?>

    <?php // echo $form->field($model, 'date_added') ?>

    <?php // echo $form->field($model, 'date_sent') ?>

    <?php // echo $form->field($model, 'sent_status') ?>

    <?php // echo $form->field($model, 'message_read') ?>

    <?php // echo $form->field($model, 'user_deleted') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
