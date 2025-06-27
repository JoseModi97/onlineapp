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

    <div class="row">
        <div class="col-md-6">
            <?php
            $surnameOptions = ['maxlength' => true];
            if (!empty($model->surname)) {
                $surnameOptions['disabled'] = true;
            }
            ?>
            <?= $form->field($model, 'surname', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>{error}{hint}'
            ])->textInput($surnameOptions) ?>
            <?php if (!empty($model->surname)): ?>
                <?= Html::activeHiddenInput($model, 'surname') ?>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <?php
            $firstNameOptions = ['maxlength' => true];
            if (!empty($model->first_name)) {
                $firstNameOptions['disabled'] = true;
            }
            ?>
            <?= $form->field($model, 'first_name', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user"></i></span>{input}</div>{error}{hint}'
            ])->textInput($firstNameOptions) ?>
            <?php if (!empty($model->first_name)): ?>
                <?= Html::activeHiddenInput($model, 'first_name') ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'other_name', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user-tag"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'email_address', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-envelope"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'mobile_no', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-mobile-alt"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'username', [
                'template' => '{label}<div class="input-group"><span class="input-group-text"><i class="fas fa-user-circle"></i></span>{input}</div>{error}{hint}'
            ])->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="mb-3 field-appapplicantuser-profile_image_file">
                <label for="profile-image-input" class="form-label">Profile Image (PNG/JPG, 100x100px)</label>
                <input type="file" id="profile-image-input" name="AppApplicantUser[profile_image_file]" class="form-control" accept=".png,.jpg,.jpeg">
                <div id="profile-image-error" class="invalid-feedback" style="display: none;"></div>
                <?php
                $profileImagePath = $model->profile_image ? Yii::getAlias('@web/img/profile/' . $model->profile_image) : '#';
                $profileImageDisplay = $model->profile_image ? 'block' : 'none';
                ?>
                <div class="mt-2">
                    <img id="profile-image-preview"
                         src="<?= $profileImagePath ?>"
                         alt="Profile Preview"
                         style="width: 100px; height: 100px; border: 1px solid #ddd; display: <?= $profileImageDisplay ?>;"
                         data-original-src="<?= $profileImagePath ?>"
                         data-original-display="<?= $profileImageDisplay ?>">
                </div>
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
// Store the base path for profile images if needed, or determine if it's better to pass the full original URL
$originalImageSrcForJs = $model->profile_image ? Yii::getAlias('@web/img/profile/' . $model->profile_image) : "";
$this->registerJs(<<<JS
$(document).ready(function() {
    const originalProfileImageSrc = $('#profile-image-preview').data('original-src');
    const originalProfileImageDisplay = $('#profile-image-preview').data('original-display');

    function revertToOriginalImage(previewElement) {
        if (originalProfileImageSrc && originalProfileImageSrc !== '#') {
            previewElement.attr('src', originalProfileImageSrc).show();
        } else {
            previewElement.attr('src', '#').hide();
        }
    }

    $('#profile-image-input').off('change.profileImage').on('change.profileImage', function(event) {
        var fileInput = event.target;
        var file = fileInput.files[0];
        var preview = $('#profile-image-preview');
        var errorDiv = $('#profile-image-error');

        errorDiv.hide().text('');
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
                        revertToOriginalImage(preview);
                        $(fileInput).val('');
                    } else {
                         $(fileInput).removeClass('is-invalid');
                    }
                };
                image.onerror = function() {
                    errorDiv.text('Cannot load image to check dimensions. Please select a valid image file.').show();
                    $(fileInput).addClass('is-invalid');
                    revertToOriginalImage(preview);
                    $(fileInput).val('');
                };
                image.src = e.target.result;
            };
            reader.onerror = function() {
                errorDiv.text('Error reading file.').show();
                $(fileInput).addClass('is-invalid');
                revertToOriginalImage(preview);
                $(fileInput).val('');
            };

            var allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                errorDiv.text('Invalid file type. Please select a PNG or JPG image.').show();
                $(fileInput).addClass('is-invalid');
                revertToOriginalImage(preview);
                $(fileInput).val('');
                return;
            }
            reader.readAsDataURL(file);
        } else { // No file selected or selection cancelled
            revertToOriginalImage(preview);
        }
    });

    // Initial setup for preview based on data attributes
    if (originalProfileImageSrc && originalProfileImageSrc !== '#') {
        $('#profile-image-preview').attr('src', originalProfileImageSrc).css('display', originalProfileImageDisplay);
    } else {
        $('#profile-image-preview').attr('src', '#').hide();
    }
});
JS, \yii\web\View::POS_READY, 'profile-image-preview-js-personal-details');
?>
