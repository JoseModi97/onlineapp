<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */
?>

<div class="account-settings-form">

    <?php $form = ActiveForm::begin([
        'id' => 'account-settings-form',
    ]); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?php $form->field($model, 'password')->passwordInput(['maxlength' => true, 'autocomplete' => 'new-password', 'placeholder' => ($model->isNewRecord ? '' : 'Leave blank if not changing')]) ?>

    <?php
    // The 'change_pass' field in the original model was a hidden input then a checkbox.
    // Restoring to a checkbox seems more user-friendly if password change is optional on update.
    // If $model->isNewRecord, password is required, so 'change_pass' might not be as relevant,
    // but for updates, it indicates intent.
    // The model's SCENARIO_STEP_ACCOUNT_SETTINGS should handle validation based on this.
    ?>
    <?php $form->field($model, 'change_pass')->checkbox(['label' => 'Set/Change Password']) ?>


    <?= $form->field($model, 'profile_image')->textInput(['maxlength' => true]) // Consider using a file input widget here 
    ?>
    <?php // Example: $form->field($model, 'profileImageFile')->fileInput() if using a file upload approach 
    ?>


    <?php // Navigation buttons are now handled by the main update-wizard.php view and AJAX JS ?>
    <div class="form-group visually-hidden">
        <?= Html::submitButton('Submit', ['style' => 'display:none;']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>