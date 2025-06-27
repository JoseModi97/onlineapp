<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppNotifications $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-notifications-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'applicant_id')->textInput() ?>

    <?= $form->field($model, 'application_ref_no')->textInput() ?>

    <?= $form->field($model, 'notification_type')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'recipient')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'sender')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'subject')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'message')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'date_added')->textInput() ?>

    <?= $form->field($model, 'date_sent')->textInput() ?>

    <?= $form->field($model, 'sent_status')->textInput() ?>

    <?= $form->field($model, 'message_read')->textInput() ?>

    <?= $form->field($model, 'user_deleted')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
