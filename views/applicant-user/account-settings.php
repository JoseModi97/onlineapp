<?php

use yii\helpers\Html;
// Removed: use yii\widgets\ActiveForm; // ActiveForm is now started in update-wizard.php

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser|null $model Nullable if not the active step for this model */
/** @var yii\widgets\ActiveForm $form Passed from update-wizard.php */
?>

<div class="account-settings-form">

    <?php // ActiveForm::begin() is removed. The form is started in update-wizard.php ?>

    <?php if ($model): // Check if model is provided for this step ?>
        <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'autocomplete' => 'new-password']) ?>

        <?php
        // Ensure 'change_pass' is treated as boolean for checkbox
        // If $model->change_pass is null or not set, it might not correctly reflect in checkbox
        // Defaulting to 0 or 1 might be needed if scenarios imply it.
        // For now, assume model handles its type.
        ?>
        <?= $form->field($model, 'change_pass')->checkbox(['label' => 'Set/Change Password']) ?>

        <?= $form->field($model, 'profile_image')->textInput(['maxlength' => true]) // Consider using a file input widget here ?>
        <?php // Example: $form->field($model, 'profileImageFile')->fileInput() if using a file upload approach ?>
    <?php else: ?>
        <p>Account settings form is not available at this step.</p>
    <?php endif; ?>

    <?php // Submit buttons are removed. They are now in update-wizard.php ?>

    <?php // ActiveForm::end() is removed. The form is ended in update-wizard.php ?>

</div>