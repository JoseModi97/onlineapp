<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="account-settings-form">

    <?php $form = ActiveForm::begin([
        'id' => 'account-settings-form',
    ]); ?>

    <?= $form->field($model, 'username')->textInput(['maxlength' => true]) ?>

    <?php // Conditional password field based on change_pass or if it's a new record (though new record password might be on signup)
    // For simplicity in wizard, assume password can always be set/changed here.
    // Logic for 'change_pass' checkbox affecting visibility/requirement can be added with JS if complex,
    // or handled by model validation rules based on scenario.
    ?>
    <?= $form->field($model, 'password')->passwordInput(['maxlength' => true, 'autocomplete' => 'new-password']) ?>
    <?= $form->field($model, 'change_pass')->hiddenInput(['value' => 1])->label(false) ?> <?php // Assuming if they are on this step and provide a password, they intend to change/set it. Or make it a visible checkbox. Let's make it visible for clarity. 
                                                                                            ?>
    <?= $form->field($model, 'change_pass')->checkbox(['label' => 'Set/Change Password']) ?>


    <?= $form->field($model, 'profile_image')->textInput(['maxlength' => true]) // Consider using a file input widget here 
    ?>
    <?php // Example: $form->field($model, 'profileImageFile')->fileInput() if using a file upload approach 
    ?>


    <div class="form-group">
        <?= Html::submitButton('<i class="fas fa-arrow-left"></i> Previous', ['class' => 'btn btn-info', 'name' => 'wizard_previous', 'formnovalidate' => true]) ?>
        <?= Html::submitButton('<i class="fas fa-save"></i> Save Application', ['class' => 'btn btn-success', 'name' => 'wizard_save']) ?>
        <?= Html::submitButton('<i class="fas fa-times"></i> Cancel', ['class' => 'btn btn-secondary', 'name' => 'wizard_cancel', 'formnovalidate' => true]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>