<?php

use yii\helpers\Html;
use yii\bootstrap5\Nav; // Using Bootstrap 5 Nav widget for tabs

/** @var yii\web\View $this */
/** @var string $currentStep The name of the current step view to render (e.g., 'personal-details') */
/** @var app\models\AppApplicantUser $model */
/** @var app\models\AppApplicant $appApplicantModel */
/** @var array $stepData Custom data passed from controller, like messages or validation states */
/** @var array $steps Array of step names/keys passed from controller */

$this->title = 'Applicant Update Wizard';
// $this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];

// Ensure $model is not null before accessing its properties for breadcrumbs
// if ($model && $model->hasAttribute('applicant_user_id') && !$model->isNewRecord) {
//     $this->params['breadcrumbs'][] = ['label' => $model->applicant_user_id, 'url' => ['view', 'applicant_user_id' => $model->applicant_user_id]];
// } elseif (Yii::$app->session->get('applicant_wizard_applicant_user_id')) {
//     // Fallback if $model is new but we have an ID from session (e.g. after first step save)
//     $this->params['breadcrumbs'][] = ['label' => Yii::$app->session->get('applicant_wizard_applicant_user_id'), 'url' => ['view', 'applicant_user_id' => Yii::$app->session->get('applicant_wizard_applicant_user_id')]];
// }
// $this->params['breadcrumbs'][] = $this->title;

// Define titles for each step for the navigation UI
$stepTitles = [
    'personal-details' => 'Personal Details',
    'applicant-specifics' => 'Applicant Specifics',
    'account-settings' => 'Account Settings',
];

$navItems = [];
$applicantUserIdForNav = $model->applicant_user_id ?? Yii::$app->session->get('applicant_wizard_applicant_user_id');

// Find the index of the current step
$currentStepIndex = array_search($currentStep, $steps);
if ($currentStepIndex === false && !empty($steps)) {
    // Default to the first step if currentStep is not found or is empty,
    // which can happen at the beginning or end of the wizard.
    // If wizard is "done" ($currentStep is null/empty), all tabs can be considered "past" or accessible.
    // For disabling purposes, setting it beyond the last step effectively enables all.
    $currentStepIndex = $currentStep === null ? count($steps) : 0;
}

// Prepare data for JavaScript
$this->registerJsFile(Yii::getAlias('@web/js/applicant-wizard-ajax.js'), ['depends' => [\yii\web\JqueryAsset::class, \yii\bootstrap5\BootstrapPluginAsset::class]]);

$jsStepsArray = json_encode(array_values($steps)); // Ensure it's a simple array for JS
$this->registerJs("
    $('.app-applicant-user-update-wizard').data('steps-array', {$jsStepsArray});
    $('.app-applicant-user-update-wizard').data('applicant-user-id', " . json_encode($applicantUserIdForNav) . ");
    // The initial currentStep is implicitly known by the active tab.
", \yii\web\View::POS_READY, 'wizard-init-data');


foreach ($steps as $index => $stepKey) {
    $isFutureStep = $index > $currentStepIndex;
    $isDisabled = $isFutureStep;

    if (!$applicantUserIdForNav && $index > 0) {
        $isDisabled = true;
    } else {
        $isDisabled = $isFutureStep;
    }
    if ($stepKey === $currentStep) {
        $isDisabled = false;
    }

    $linkOptions = ['data-step' => $stepKey, 'class' => 'nav-link']; // Add data-step for JS
    if ($isDisabled) {
        $linkOptions['class'] .= ' disabled-link disabled'; // Keep initial disabled state visual
        $linkOptions['tabindex'] = "-1";
        $linkOptions['aria-disabled'] = "true";
    }

    $navItems[] = [
        'label' => $stepTitles[$stepKey] ?? ucfirst(str_replace('-', ' ', $stepKey)),
        'url' => 'javascript:void(0);', // AJAX will handle navigation
        'active' => $currentStep === $stepKey,
        'disabled' => $isDisabled, // Still useful for initial state and non-JS, though JS will override
        'linkOptions' => $linkOptions,
    ];
}
?>
<div class="app-applicant-user-update-wizard" data-applicant-user-id="<?= Html::encode($applicantUserIdForNav) ?>" data-steps-array="<?= Html::encode(json_encode(array_values($steps))) ?>">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php
    // Display tab navigation
    if (!empty($navItems)) {
        echo Nav::widget([
            'options' => ['class' => 'nav nav-tabs mb-3'],
            'items' => $navItems,
        ]);
    }
    ?>

    <?php // Flash messages
    if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger">
            <?= Yii::$app->session->getFlash('error') ?>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('info')): ?>
        <div class="alert alert-info">
            <?= Yii::$app->session->getFlash('info') ?>
        </div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('success') && ($currentStep === null || empty($currentStep))): // Show general success if wizard is "done" 
    ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php // Step-specific messages from controller
    if (isset($stepData['message']) && is_scalar($stepData['message'])): ?>
        <div class="alert alert-danger" id="wizard-server-message"><?= Html::encode($stepData['message']) ?></div>
    <?php endif; ?>

    <div id="wizard-general-error" class="alert alert-danger" style="display:none;"></div>

    <div id="wizard-step-content">
        <?php // Render the initial step's content
        if ($currentStep && is_string($currentStep) && in_array($currentStep, $steps)) {
            $stepViewFile = Yii::getAlias('@app/views/applicant-user/' . $currentStep . '.php');
            if (file_exists($stepViewFile)) {
                // Pass currentStepForView to the partial, so it knows which step it is
                // This is useful if a single partial view file is used for multiple similar steps.
                echo $this->render($currentStep, [
                    'model' => $model,
                    'appApplicantModel' => $appApplicantModel,
                    'stepData' => $stepData,
                    'currentStepForView' => $currentStep // Pass the actual current step key
                ]);
            } else {
                echo '<div class="alert alert-warning">Step view not found: ' . Html::encode($currentStep) . '</div>';
            }
        } elseif (!$currentStep && Yii::$app->session->hasFlash('success')) {
            // Wizard completed successfully (non-AJAX redirect path or initial load after completion)
            if ($applicantUserIdForNav) {
                echo Html::a('View Applicant Details', ['view', 'applicant_user_id' => $applicantUserIdForNav], ['class' => 'btn btn-primary']);
                echo ' ';
            }
            echo Html::a('Back to List', ['index'], ['class' => 'btn btn-secondary']);
        } elseif (!$currentStep && !Yii::$app->session->hasFlash('success')) {
            // Wizard cancelled or in an undefined state
            echo '<div class="alert alert-info">The wizard process has been cancelled or is in an undefined state.</div>';
            echo Html::a('Back to List', ['index'], ['class' => 'btn btn-secondary']);
        }
        ?>
    </div>

    <div class="mt-3">
        <?php
        $currentStepIdx = array_search($currentStep, $steps);
        $isFirstStep = $currentStepIdx === 0;
        $isLastStep = $currentStepIdx === (count($steps) - 1);

        echo Html::button('Previous', [
            'class' => 'btn btn-secondary me-2',
            'id' => 'wizard-previous-btn',
            'style' => $isFirstStep ? 'display:none;' : ''
        ]);
        echo Html::button('Next', [
            'class' => 'btn btn-primary me-2',
            'id' => 'wizard-next-btn',
            'style' => $isLastStep ? 'display:none;' : ''
        ]);
        // The 'Save' button might be specific to the last step, or a general 'Save Draft'
        // For now, let's assume it's for the final save on the last step.
        echo Html::button('Save', [
            'class' => 'btn btn-success',
            'id' => 'wizard-save-btn',
            'style' => !$isLastStep ? 'display:none;' : ''
        ]);
        // Cancel button could be a simple link
        // echo Html::a('Cancel', ['index'], ['class' => 'btn btn-warning ms-2', 'name' => 'wizard_cancel']);
        ?>
    </div>
</div>