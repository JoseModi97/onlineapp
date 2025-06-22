<?php

use yii\helpers\Html;
use beastbytes\wizard\WizardMenu;

/** @var yii\web\View $this */
/** @var yii\base\Event $event */ // Could be WizardEvent or StepEvent

$this->title = 'Applicant Update Wizard';
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];

// Safely access model for breadcrumbs, $event->data might not always have 'model'
// For example, afterWizard event might have different data structure or be null.
$modelForBreadcrumb = null;
if (isset($event->data['model']) && $event->data['model'] instanceof \app\models\AppApplicantUser && !$event->data['model']->isNewRecord) {
    $modelForBreadcrumb = $event->data['model'];
} elseif (Yii::$app->controller->wizard && Yii::$app->controller->wizard->read(Yii::$app->controller->wizard->sessionKey . '.applicant_user_id')) {
    // Attempt to load model if ID is in wizard session, useful if event data is not set but wizard is active
    $applicantId = Yii::$app->controller->wizard->read(Yii::$app->controller->wizard->sessionKey . '.applicant_user_id');
    if ($applicantId) {
        // Avoid direct findModel call here to keep view logic simple.
        // Breadcrumb might just show "Update" or we rely on controller to always pass necessary data.
        // For simplicity, we'll only use event data if available.
    }
}

if ($modelForBreadcrumb) {
    $this->params['breadcrumbs'][] = ['label' => $modelForBreadcrumb->applicant_user_id, 'url' => ['view', 'applicant_user_id' => $modelForBreadcrumb->applicant_user_id]];
}
$this->params['breadcrumbs'][] = $this->title;

// Get current step, ensure $event and $event->step are valid
$currentStep = null;
if ($event && property_exists($event, 'step') && $event->step) {
    $currentStep = $event->step;
} elseif (Yii::$app->controller->wizard && Yii::$app->controller->wizard->getCurrentStep()) {
    // Fallback to wizard behavior's current step if event doesn't have it (e.g. after wizard completion view)
    $currentStep = Yii::$app->controller->wizard->getCurrentStep();
}

?>
<div class="app-applicant-user-update-wizard">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if ($event && property_exists($event, 'sender') && $event->sender instanceof \beastbytes\wizard\WizardBehavior && $currentStep): ?>
        <?= WizardMenu::widget(['step' => $currentStep, 'wizard' => $event->sender]) ?>
    <?php else: ?>
        <p>Wizard navigation is currently unavailable.</p>
    <?php endif; ?>

    <?php if (isset($event->data['message']) && is_scalar($event->data['message'])): ?>
        <div class="alert alert-danger"><?= Html::encode($event->data['message']) ?></div>
    <?php endif; ?>

    <?php if ($currentStep && is_string($currentStep)): ?>
        <?php
        // Ensure the step view file exists
        $stepViewFile = Yii::getAlias('@app/views/applicant-user/' . $currentStep . '.php');
        if (file_exists($stepViewFile)) {
            echo $this->render($currentStep, ['event' => $event]);
        } else {
            echo '<div class="alert alert-warning">Step view not found: ' . Html::encode($currentStep) . '</div>';
        }
        ?>
    <?php elseif (!$currentStep && $event && property_exists($event, 'step') && $event->step === true): ?>
        <div class="alert alert-success">Wizard completed successfully!</div>
        <?php // Optionally, add a link to view the record or go to index ?>
        <?= Html::a('View Applicant', ['view', 'applicant_user_id' => Yii::$app->controller->wizard->read(Yii::$app->controller->wizard->sessionKey . '.applicant_user_id')], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-default']) ?>
    <?php else: ?>
        <div class="alert alert-info">Loading wizard step or wizard has ended.</div>
         <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
