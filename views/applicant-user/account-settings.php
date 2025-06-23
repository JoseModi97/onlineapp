<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */
?>

<div class="account-settings-form">

    <?php $form = ActiveForm::begin([
        'id' => 'account-settings-form',
        'options' => ['enctype' => 'multipart/form-data'] // Important for file uploads
    ]); ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'username', [
                'template' => "{label}\n<div class='input-group'><span class='input-group-text'><i class='fas fa-user-circle'></i></span>{input}</div>\n{hint}\n{error}",
            ])->textInput(['maxlength' => true, 'class' => 'form-control']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'password', [
                'template' => "{label}\n<div class='input-group'><span class='input-group-text'><i class='fas fa-lock'></i></span>{input}</div>\n{hint}\n{error}",
            ])->passwordInput(['maxlength' => true, 'autocomplete' => 'new-password', 'placeholder' => ($model->isNewRecord || $model->change_pass ? '' : 'Leave blank if not changing'), 'class' => 'form-control']) ?>

            <?= $form->field($model, 'change_pass', ['options' => ['class' => 'mt-2 form-check'] ])->checkbox(['label' => 'Set/Change Password', 'class' => 'form-check-input']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?php
            // For file input, input group styling is a bit different.
            // We'll customize the label and put the icon there or use a standard file input.
            // The `profile_image_file` attribute is on the model for the UploadedFile instance.
            // The `profile_image` attribute stores the filename string.
            ?>
            <?= $form->field($model, 'profile_image_file', [
                'labelOptions' => ['class' => 'form-label'],
                 // 'inputTemplate' => '<div class="input-group"><span class="input-group-text"><i class="fas fa-image"></i></span>{input}</div>' // This can look clunky for file inputs
            ])->fileInput([
                'id' => 'profile-image-input', // Keep existing ID for JS
                'class' => 'form-control',
                'accept' => '.png,.jpg,.jpeg'
            ])->label('<i class="fas fa-image"></i> Profile Image (PNG/JPG, 100x100px)') ?>

            <div id="profile-image-error" class="invalid-feedback" style="display: none;"></div>
            <div class="mt-2">
                <img id="profile-image-preview" src="<?= $model->profile_image ? Yii::getAlias('@web/img/profile/' . $model->profile_image) : '#' ?>" alt="Profile Preview" style="width: 100px; height: 100px; border: 1px solid #ddd; display: <?= $model->profile_image ? 'block' : 'none' ?>;">
            </div>
        </div>
    </div>

    <?php // Navigation buttons are now handled by the main update-wizard.php view and AJAX JS ?>
    <div class="form-group visually-hidden">
        <?= Html::submitButton('Submit', ['style' => 'display:none;']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs(<<<JS
$(document).ready(function() {
    $('#profile-image-input').on('change', function(event) {
        var fileInput = event.target;
        var file = fileInput.files[0];
        var preview = $('#profile-image-preview');
        var errorDiv = $('#profile-image-error');

        errorDiv.hide().text('');
        preview.hide();
        $(fileInput).removeClass('is-invalid');

        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.attr('src', e.target.result).show();

                var image = new Image();
                image.onload = function() {
                    if (image.width !== 100 || image.height !== 100) {
                        errorDiv.text('Image must be 100x100 pixels. Selected: ' + image.width + 'x' + image.height + 'px.').show();
                        $(fileInput).addClass('is-invalid');
                        // preview.hide(); // Optionally hide preview if dimensions are wrong
                        // $(fileInput).val(''); // Clear the file input
                    } else {
                         $(fileInput).removeClass('is-invalid'); // Explicitly remove if it was added
                    }
                };
                image.onerror = function() {
                    errorDiv.text('Cannot load image to check dimensions. Please select a valid image file.').show();
                    $(fileInput).addClass('is-invalid');
                    // preview.hide();
                    // $(fileInput).val('');
                };
                image.src = e.target.result;
            };
            reader.onerror = function() {
                errorDiv.text('Error reading file.').show();
                $(fileInput).addClass('is-invalid');
                // $(fileInput).val('');
            };

            // Client-side type check (basic)
            var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                errorDiv.text('Invalid file type. Please select a PNG or JPG image.').show();
                $(fileInput).addClass('is-invalid');
                // $(fileInput).val(''); // Clear the file input
                return; // Stop further processing
            }

            reader.readAsDataURL(file);
        } else {
            // No file selected or file selection cancelled
            preview.attr('src', '#').hide();
             // If there was a previously valid image and user cancels selection,
             // decide if you want to show the old one or keep preview hidden.
             // For now, it just hides.
        }
    });

    // Preserve existing image preview on initial load if model has an image
    var existingImageSrc = $('#profile-image-preview').attr('src');
    if (existingImageSrc && existingImageSrc !== '#') {
        $('#profile-image-preview').show();
    }
});
JS, \yii\web\View::POS_READY, 'profile-image-preview-js');
?>