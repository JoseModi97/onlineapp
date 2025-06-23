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


    <div class="mb-3">
        <label for="profile-image-input" class="form-label">Profile Image (PNG/JPG, 100x100px)</label>
        <input type="file" id="profile-image-input" name="AppApplicantUser[profile_image_file]" class="form-control" accept=".png,.jpg,.jpeg">
        <div id="profile-image-error" class="invalid-feedback" style="display: none;"></div>
        <div class="mt-2">
            <img id="profile-image-preview" src="<?= $model->profile_image ? Yii::getAlias('@web/img/profile/' . $model->profile_image) : '#' ?>" alt="Profile Preview" style="width: 100px; height: 100px; border: 1px solid #ddd; display: <?= $model->profile_image ? 'block' : 'none' ?>;">
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