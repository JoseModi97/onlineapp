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

    <?= $form->field($model, 'surname')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'other_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email_address')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true]) ?>

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
