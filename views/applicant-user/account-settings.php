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

    <?php // Username and Profile Image fields have been moved to personal-details.php ?>

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