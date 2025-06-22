<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm; // Ensure ActiveForm is used for the main form wrapper

/** @var yii\web\View $this */
/** @var string $currentStep */
/** @var app\models\AppApplicantUser $model */
/** @var app\models\AppApplicant $appApplicantModel */
/** @var array $stepData Custom data passed from controller, like messages */
/** @var array $steps Array of step names passed from controller */

$this->title = 'Applicant Update Wizard';
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];

if ($model && !$model->isNewRecord) {
    $this->params['breadcrumbs'][] = ['label' => $model->applicant_user_id, 'url' => ['view', 'applicant_user_id' => $model->applicant_user_id]];
}
$this->params['breadcrumbs'][] = $this->title;

// This will be used by the multi-step form JS
$currentStepIndex = array_search($currentStep, $steps);
if ($currentStepIndex === false) {
    $currentStepIndex = 0; // Default to first step if not found
}

?>
<div class="app-applicant-user-update-wizard">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('info')): ?>
        <div class="alert alert-info">
            <?= Yii::$app->session->getFlash('info') ?>
        </div>
    <?php endif; ?>
    <?php if (isset($stepData['message']) && is_scalar($stepData['message'])): ?>
        <div class="alert alert-danger"><?= Html::encode($stepData['message']) ?></div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'applicant-update-wizard-form',
        // The action will be the current URL, which is the update-wizard action itself.
        // Method is POST by default.
    ]); ?>

    <?php
    // Render step views, wrapping each in a 'tab' div
    // The controller will still determine which models/data are primarily active for POST,
    // but all forms need to be within the main $form
    foreach ($steps as $stepName) {
        echo '<div class="tab">'; // Each step content is a "tab" for the JS plugin
        $stepViewFile = Yii::getAlias('@app/views/applicant-user/' . $stepName . '.php');
        if (file_exists($stepViewFile)) {
            // We need to pass the main form object to the step views
            // so that their fields are part of this single form.
            // However, the step views use their own ActiveForm::begin(). This needs to be changed.
            // For now, let's render them. The next step will be to modify step views.
            echo $this->render($stepName, [
                'model' => ($stepName === 'personal-details' || $stepName === 'account-settings') ? $model : null, // Pass the correct model
                'appApplicantModel' => ($stepName === 'applicant-specifics') ? $appApplicantModel : null,
                'form' => $form, // Pass the main form object
                'isCurrentStep' => ($currentStep === $stepName), // To help step views manage active state if needed
                'stepData' => $stepData,
            ]);
        } else {
            echo '<div class="alert alert-warning">Step view not found: ' . Html::encode($stepName) . '</div>';
        }
        echo '</div>';
    }
    ?>

    <div style="overflow:auto; margin-top: 20px;">
        <div style="float:right;">
            <button type="button" class="previous btn btn-secondary">Previous</button>
            <button type="button" class="next btn btn-primary">Next</button>
            <?php // The submit button for the form. Name it 'wizard_save' to match controller logic. ?>
            <?= Html::submitButton('<i class="fas fa-save"></i> Save Application', ['class' => 'submit btn btn-success', 'name' => 'wizard_save']) ?>
            <?= Html::submitButton('<i class="fas fa-times"></i> Cancel', ['class' => 'btn btn-danger', 'name' => 'wizard_cancel', 'formnovalidate' => true, 'style' => 'margin-left: 5px;']) ?>
        </div>
    </div>

    <!-- Circles which indicates the steps of the form: -->
    <div style="text-align:center;margin-top:40px;">
        <?php foreach ($steps as $index => $stepName): ?>
            <span class="step"><?= $index + 1 ?></span>
        <?php endforeach; ?>
    </div>

    <?php ActiveForm::end(); ?>

    <?php // Display success message if wizard completed (less likely here with JS nav, but good fallback) ?>
    <?php if (Yii::$app->session->hasFlash('success') && (!$currentStep || !is_string($currentStep))): ?>
        <div class="alert alert-success" style="margin-top:20px;">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

</div>

<?php
// Initialize the multiStepForm plugin
// We pass the current step index to the navigateTo function.
$this->registerJs("
    var wizardForm = $('#applicant-update-wizard-form');
    wizardForm.multiStepForm({
        // We will not use multiStepForm's validation, Yii's validation will be used.
        // noValidate: true, // This option doesn't exist in the plugin, need to modify plugin or rely on Yii
        // We need to handle submissions carefully.
        // The plugin's 'submit' button will trigger the form submission.
        // The controller's 'wizard_save' logic should handle the final save.
        // 'Next' and 'Previous' clicks are handled by the plugin.
        // We need to ensure that Yii's validation is triggered before allowing 'Next'.
        // This might require custom JS to integrate with Yii's ActiveForm client validation.

        // For now, let's handle basic navigation.
        // The actual form submission for next/previous for saving intermediate steps
        // will need to be changed. The plugin assumes client-side only navigation between steps
        // until the final submit.

        // Let's modify the 'next' button behavior to submit the form for the current step
        // This is a deviation from the plugin's typical use but aligns with current backend.
    })
    .navigateTo({$currentStepIndex}); // Navigate to the current step based on controller

    // Override plugin's next button behavior for now to trigger Yii form submission for current step
    // This is complex because the plugin wants to control visibility.
    // A better approach might be to use AJAX for step saving if we want client-side feel
    // or stick to full page reloads for each step with Yii handling state.

    // Given the existing controller saves data on 'wizard_next',
    // the 'next' button from the plugin should probably trigger a form submit
    // with a specific input that indicates 'wizard_next' for the *current* visible step.
    // This is tricky as the plugin hides/shows tabs.

    // Let's try a simpler approach first: the plugin handles client-side navigation.
    // Yii validation will occur on the final submit.
    // This means intermediate saves on 'next' are lost with the pure JS plugin.

    // To keep intermediate saves:
    // We might need to make the 'Next' button a submit button for the main form,
    // and have the controller determine which step's data to process and save,
    // then redirect back to the wizard, telling the JS which step to display.

    // For this iteration, let's use the plugin for client-side navigation only.
    // The 'Save Application' button will be the one that submits all data.
    // This changes the current behavior of saving per step.

    // If per-step saving is crucial, we need to:
    // 1. Make 'Next' button a submit button (e.g., name='wizard_next_js').
    // 2. JS on 'Next' click:
    //    - Ensure current tab's data is part of the POST.
    //    - Submit the form.
    // 3. Controller:
    //    - Detect 'wizard_next_js'.
    //    - Save current step's data.
    //    - Redirect back, setting the next step.
    //    - JS on page load navigates to the correct step.

    // --- Updated JS for client-side validation using beforeNext ---

    wizardForm.multiStepForm({
        beforeNext: function(currentIndex, currentTabElement) {
            // 'this' inside this callback refers to the form element, thanks to .call(form,...) in the plugin
            var yiiForm = $(this);

            // Trigger Yii's client-side validation for the whole form
            yiiForm.yiiActiveForm('validate');

            // Crucially, return false here to prevent the plugin from navigating immediately.
            // Navigation will be handled in the 'afterValidate' event if validation passes.
            return false;
        }
    }).navigateTo(<?php echo $currentStepIndex; ?>); // Navigate to the initial step

    // Setup the afterValidate event handler for the form
    // This handler will decide whether to navigate after validation is complete
    wizardForm.off('afterValidate.customWizardValidation').on('afterValidate.customWizardValidation', function (event, messages, errorAttributes) {
        // 'this' here is the form element
        var yiiForm = $(this);
        var currentTab = yiiForm.find('.tab.current'); // Get the current tab again
        var currentTabHasErrors = false;

        if (errorAttributes && errorAttributes.length > 0) {
            $.each(errorAttributes, function (i, attr) {
                // Check if the field with error (attr.id) is within the currentTab
                if (currentTab.find('#' + attr.id).length > 0) {
                    currentTabHasErrors = true;
                    return false; // Break loop, error found in current tab
                }
            });
        }

        if (!currentTabHasErrors) {
            var tabs = yiiForm.find('.tab');
            // Determine current index based on the .current class, as 'currentIndex' from beforeNext might be stale if validation took time
            var currentIndex = tabs.index(currentTab);
            if (currentIndex < tabs.length - 1) {
                // Call the plugin's navigateTo method.
                yiiForm.navigateTo(currentIndex + 1);
            }
        } else {
            // Errors exist in the current tab, do not navigate.
            // Focus the first field with an error in the current tab for better UX
            var firstErrorField = null;
            if (errorAttributes && errorAttributes.length > 0) {
                $.each(errorAttributes, function (i, attr) {
                    var fieldInTab = currentTab.find('#' + attr.id);
                    if (fieldInTab.length > 0) {
                        firstErrorField = fieldInTab;
                        return false; // Break loop
                    }
                });
            }
            if (firstErrorField && firstErrorField.length > 0) {
                firstErrorField.first().focus();
            } else {
                 // Fallback: if specific error field not found in tab, but tab has errors
                 currentTab.find('.is-invalid:visible').first().focus();
            }
        }
    });

", \yii\web\View::POS_READY);
?>
