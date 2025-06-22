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
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];

// Ensure $model is not null before accessing its properties for breadcrumbs
if ($model && $model->hasAttribute('applicant_user_id') && !$model->isNewRecord) {
    $this->params['breadcrumbs'][] = ['label' => $model->applicant_user_id, 'url' => ['view', 'applicant_user_id' => $model->applicant_user_id]];
} elseif (Yii::$app->session->get('applicant_wizard_applicant_user_id')) {
    // Fallback if $model is new but we have an ID from session (e.g. after first step save)
     $this->params['breadcrumbs'][] = ['label' => Yii::$app->session->get('applicant_wizard_applicant_user_id'), 'url' => ['view', 'applicant_user_id' => Yii::$app->session->get('applicant_wizard_applicant_user_id')]];
}
$this->params['breadcrumbs'][] = $this->title;

// Define titles for each step for the navigation UI
$stepTitles = [
    'personal-details' => 'Personal Details',
    'applicant-specifics' => 'Applicant Specifics',
    'account-settings' => 'Account Settings',
];

$navItems = [];
$applicantUserIdForNav = $model->applicant_user_id ?? Yii::$app->session->get('applicant_wizard_applicant_user_id');

foreach ($steps as $stepKey) {
    $navItems[] = [
        'label' => $stepTitles[$stepKey] ?? ucfirst(str_replace('-', ' ', $stepKey)),
        'url' => ['update-wizard', 'currentStep' => $stepKey, 'applicant_user_id' => $applicantUserIdForNav],
        'active' => $currentStep === $stepKey,
        // To implement disabling future steps, you'd need logic to check if a step is "unlocked"
        // For example, by checking if previous steps' data exists in session or if applicant_user_id is set.
        // 'disabled' => ($stepKey !== self::STEP_PERSONAL_DETAILS && !$applicantUserIdForNav) // Example: disable if not first step and no user ID
    ];
}
?>
<div class="app-applicant-user-update-wizard">

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
    <?php if (Yii::$app->session->hasFlash('success') && ($currentStep === null || empty($currentStep)) ): // Show general success if wizard is "done" ?>
        <div class="alert alert-success">
            <?= Yii::$app->session->getFlash('success') ?>
        </div>
    <?php endif; ?>

    <?php // Step-specific messages from controller
    if (isset($stepData['message']) && is_scalar($stepData['message'])): ?>
        <div class="alert alert-danger"><?= Html::encode($stepData['message']) ?></div>
    <?php endif; ?>


    <?php // Render the current step's content
    if ($currentStep && is_string($currentStep) && in_array($currentStep, $steps)) {
        $stepViewFile = Yii::getAlias('@app/views/applicant-user/' . $currentStep . '.php');
        if (file_exists($stepViewFile)) {
            echo $this->render($currentStep, [
                'model' => $model,
                'appApplicantModel' => $appApplicantModel,
                'stepData' => $stepData,
            ]);
        } else {
            echo '<div class="alert alert-warning">Step view not found: ' . Html::encode($currentStep) . '</div>';
        }
    } elseif (!$currentStep && Yii::$app->session->hasFlash('success')) {
        // Wizard completed successfully
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
