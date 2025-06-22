<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var beastbytes\wizard\WizardEvent $event */
/** @var app\models\AppApplicantUser $model */

$model = $event->data['model'];
?>

<div class="account-settings-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'change_pass')->checkbox() ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'profile_image')->textInput(['maxlength' => true]) // Consider using a file input widget here ?>


    <div class="form-group">
        <?= Html::submitButton('Previous', ['class' => 'btn btn-default', 'name' => 'wizard_previous']) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'name' => 'wizard_save']) ?>
        <?= Html::submitButton('Cancel', ['class' => 'btn btn-default', 'name' => 'wizard_cancel']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
