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
        <?= Html::submitButton('Previous', ['class' => 'btn btn-default', 'name' => $event->action->buttonName(beastbytes\wizard\WizardBehavior::BUTTON_PREVIOUS)]) ?>
        <?= Html::submitButton('Save', ['class' => 'btn btn-success', 'name' => $event->action->buttonName(beastbytes\wizard\WizardBehavior::BUTTON_SAVE)]) ?>
        <?= Html::submitButton('Cancel', ['class' => 'btn btn-default', 'name' => $event->action->buttonName(beastbytes\wizard\WizardBehavior::BUTTON_CANCEL)]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
