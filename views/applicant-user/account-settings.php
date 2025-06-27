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

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info" role="alert">
                User profile image and username settings have been moved to the "Personal Details" step.
            </div>
        </div>
    </div>

    <?php // Hidden field to store the actual image filename for the model, if needed, or handle directly in controller
    // For now, the controller will handle the 'profile_image_file' and update 'profile_image' attribute.
    // echo $form->field($model, 'profile_image')->hiddenInput()->label(false);
    ?>

    <?php // Navigation buttons are now handled by the main update-wizard.php view and AJAX JS
    ?>
    <div class="form-group visually-hidden">
        <?= Html::submitButton('Submit', ['style' => 'display:none;']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// JavaScript for profile image preview has been moved to personal-details.php
?>