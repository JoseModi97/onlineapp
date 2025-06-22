<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */
/** @var app\models\AppApplicant $appApplicantModel */
/** @var yii\widgets\ActiveForm $form */

$this->registerCss("
    .tab-pane { padding-top: 15px; }
    .nav-tabs > li > a { cursor: pointer; }
    .nav-tabs > li.disabled > a {
        cursor: not-allowed;
        color: #999;
    }
    .nav-tabs > li.disabled > a:hover,
    .nav-tabs > li.disabled > a:focus {
        border-color: transparent;
        background-color: transparent;
        color: #999;
    }
");
?>

<div class="app-applicant-user-form-wizard">

    <?php $form = ActiveForm::begin([
        'id' => 'applicant-wizard-form',
        // 'enableAjaxValidation' => true, // Enable if you want AJAX validation for each field on blur/change
        // 'validateOnChange' => true,
        // 'validateOnBlur' => true,
    ]); ?>

    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist" id="wizardTabs">
        <li role="presentation" class="active"><a href="#personalDetails" aria-controls="personalDetails" role="tab" data-toggle="tab">Personal Details</a></li>
        <li role="presentation" class="disabled"><a href="#applicantSpecifics" aria-controls="applicantSpecifics" role="tab" data-toggle="tab">Applicant Specifics</a></li>
        <li role="presentation" class="disabled"><a href="#accountSettings" aria-controls="accountSettings" role="tab" data-toggle="tab">Account Settings</a></li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="personalDetails">
            <?= $form->field($model, 'surname')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'other_name')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'email_address')->textInput(['maxlength' => true]) ?>
            <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true]) ?>

            <div class="form-group">
                <button type="button" class="btn btn-primary next-tab">Next</button>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="applicantSpecifics">
            <?= $form->field($appApplicantModel, 'gender')->dropDownList(['Male' => 'Male', 'Female' => 'Female'], ['prompt' => 'Select Gender']) ?>
            <?= $form->field($appApplicantModel, 'dob')->widget(\yii\jui\DatePicker::class, [
                'options' => ['class' => 'form-control'],
                'dateFormat' => 'php:Y-m-d',
            ]) ?>
            <?= $form->field($appApplicantModel, 'religion')->textInput(['maxlength' => true]) ?>
            <?= $form->field($appApplicantModel, 'country_code')->textInput(['type' => 'number']) ?>
            <?= $form->field($appApplicantModel, 'national_id')->textInput(['type' => 'number']) ?>
            <?= $form->field($appApplicantModel, 'marital_status')->dropDownList([
                'Single' => 'Single',
                'Married' => 'Married',
                'Divorced' => 'Divorced',
                'Widowed' => 'Widowed',
            ], ['prompt' => 'Select Marital Status']) ?>

            <div class="form-group">
                <button type="button" class="btn btn-default prev-tab">Previous</button>
                <button type="button" class="btn btn-primary next-tab">Next</button>
            </div>
        </div>

        <div role="tabpanel" class="tab-pane" id="accountSettings">
            <?php if ($model->isNewRecord || !empty($model->password) || !empty($model->activation_code)): ?>
                <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
            <?php endif; ?>
            <?= $form->field($model, 'profile_image')->textInput(['maxlength' => true]) // Consider using a file input widget here ?>
            <?= $form->field($model, 'username')->textInput() ?>
            <?= $form->field($model, 'change_pass')->checkbox() ?>

            <div class="form-group">
                <button type="button" class="btn btn-default prev-tab">Previous</button>
                <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJs(<<<JS
$(document).ready(function () {
    var \$form = $('#applicant-wizard-form');
    var \$tabs = $('#wizardTabs li');
    var \$tabPanes = $('.tab-pane');

    function activateTab(\$tabLink) {
        \$tabLink.parent().removeClass('disabled').find('a').tab('show');
    }

    function validateTabFields(\$currentTabPane) {
        var validate = true;
        // Find all visible input fields within the current tab pane that are subject to validation
        \$currentTabPane.find('input:visible, select:visible, textarea:visible').each(function () {
            var \$field = $(this);
            // Trigger validation for the specific field
            // Note: field must have an ID for yiiActiveForm to work on it directly.
            // Yii ActiveForm typically assigns IDs.
            if (\$(this).attr('id')) {
                 \$form.yiiActiveForm('validateAttribute', \$(this).attr('id'));
            }
        });

        // After triggering validation, check for errors within the current tab
        // Check .has-error on .form-group elements
        \$currentTabPane.find('.form-group').each(function() {
            if (\$(this).hasClass('has-error')) {
                validate = false;
                return false; // break loop
            }
        });
        return validate;
    }

    // Handle Next button click
    $('.next-tab').on('click', function () {
        var \$currentTab = $(this).closest('.tab-pane');
        var \$currentTabLink = $('#wizardTabs li a[href="#' + \$currentTab.attr('id') + '"]');
        var \$nextTabLink = \$currentTabLink.parent().next('li').find('a');

        if (validateTabFields(\$currentTab)) {
            if (\$nextTabLink.length) {
                activateTab(\$nextTabLink);
                // Optionally, disable clicking on previous tab headers once moved forward
                // \$currentTabLink.parent().addClass('disabled');
            }
        }
    });

    // Handle Previous button click
    $('.prev-tab').on('click', function () {
        var \$currentTab = $(this).closest('.tab-pane');
        var \$prevTabLink = $('#wizardTabs li a[href="#' + \$currentTab.attr('id') + '"]').parent().prev('li').find('a');
        if (\$prevTabLink.length) {
            activateTab(\$prevTabLink);
        }
    });

    // Handle tab header click
    $('#wizardTabs a[data-toggle="tab"]').on('click', function (e) {
        var \$this = $(this);
        var \$li = \$this.parent();

        if (\$li.hasClass('disabled')) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }

        // Optional: If you want to re-validate when clicking already "unlocked" tabs.
        // Generally, not needed if forward progression is the main validation point.
        // var \$targetPane = \$(\$this.attr('href'));
        // var currentPaneValidated = true;
        //
        // // Check if we are trying to move forward by clicking a tab header
        // var targetIndex = \$li.index();
        // var activeIndex = \$('#wizardTabs li.active').index();
        //
        // if (targetIndex > activeIndex) {
        //     for (var i = activeIndex; i < targetIndex; i++) {
        //         var paneToValidate = \$tabPanes.eq(i);
        //         if (!validateTabFields(paneToValidate)) {
        //             currentPaneValidated = false;
        //             // Show the last validated/failed tab instead of the clicked one
        //             $('#wizardTabs li a').eq(i).tab('show');
        //             e.preventDefault();
        //             e.stopImmediatePropagation();
        //             break;
        //         }
        //         // Mark intermediate tabs as enabled if validation passes (though 'next' button already does this)
        //          $('#wizardTabs li').eq(i+1).removeClass('disabled');
        //     }
        // }
    });

    // Prevent form submission when "Next" or "Previous" buttons are clicked (they are type="button")
    // And also when enter is pressed in a field, if not on the last step.
    \$form.on('beforeSubmit', function(e) {
        // This event is triggered by Yii after successful client-side validation
        // when the submit button (Html::submitButton) is clicked.
        // If we are on the last tab, allow submission. Otherwise, it might be an Enter key press.
        var \$activeTabPane = \$('.tab-pane.active');
        if (!\$activeTabPane.is(\$('#accountSettings'))) { // ID of the last tab pane
            // If not the last tab, and the submit was triggered by something other than the actual save button
            // (e.g. enter key in a field), we should prevent it.
            // However, 'beforeSubmit' is usually only for the main submit button.
            // Validation for next buttons is handled separately.
        }
        return true; // Allow submission if it's the actual save button
    });

    // Initialize: disable all tabs except the first one.
    // This is already done by adding 'disabled' class in HTML,
    // but JS can reinforce this if needed or manage complex states.
    // \$tabs.not('.active').addClass('disabled');

    // Ensure that Yii's validation messages are shown
    \$form.on('afterValidateAttribute', function (event, attribute, messages) {
        // This event is useful for debugging or custom handling after an attribute is validated.
        // Yii automatically shows/hides error messages.
    });

});
JS
);
?>