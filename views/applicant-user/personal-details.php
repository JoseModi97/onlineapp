<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var beastbytes\wizard\WizardEvent $event */
/** @var app\models\AppApplicantUser $model */

$model = $event->data['model'];
?>

<div class="personal-details-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'surname')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'other_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email_address')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Next', ['class' => 'btn btn-primary', 'name' => $event->action->buttonName(beastbytes\wizard\WizardBehavior::BUTTON_NEXT)]) ?>
        <?= Html::submitButton('Cancel', ['class' => 'btn btn-default', 'name' => $event->action->buttonName(beastbytes\wizard\WizardBehavior::BUTTON_CANCEL)]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
