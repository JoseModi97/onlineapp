<?php

use yii\helpers\Html;

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

$stepTitles = [
    'personal-details' => 'Personal Details',
    'applicant-specifics' => 'Applicant Specifics',
    'account-settings' => 'Account Settings',
];

?>
<div class="app-applicant-user-update-wizard">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php // More robust step navigation UI ?>
    <ul class="nav nav-tabs" style="margin-bottom: 20px;">
        <?php foreach ($steps as $stepName): ?>
            <li role="presentation" class="<?= $currentStep === $stepName ? 'active' : '' ?>">
                <?php
                // Create a non-clickable tab for future steps if not yet accessible or for past steps if preferred.
                // For simplicity here, all steps are linked.
                // Logic can be added to disable future steps if needed.
                $link = Html::a($stepTitles[$stepName] ?? ucfirst(str_replace('-', ' ', $stepName)),
                    ['update-wizard', 'currentStep' => $stepName, 'applicant_user_id' => $model->applicant_user_id ?? Yii::$app->session->get('applicant_wizard_applicant_user_id')]
                );
                // If it's the current step, make it a span or just text, not a link
                // However, Yii's Nav widget usually handles this by making active tab non-link or visually distinct.
                // For manual tabs, let's keep them as links to allow refresh or explicit navigation if desired.
                echo $link;
                ?>
            </li>
        <?php endforeach; ?>
    </ul>

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


    <?php if ($currentStep && is_string($currentStep)): ?>
        <?php
        $stepViewFile = Yii::getAlias('@app/views/applicant-user/' . $currentStep . '.php');
        if (file_exists($stepViewFile)) {
            echo $this->render($currentStep, [
                'model' => $model,
                'appApplicantModel' => $appApplicantModel,
                'stepData' => $stepData, // Pass along for any specific messages within step views
            ]);
        } else {
            echo '<div class="alert alert-warning">Step view not found: ' . Html::encode($currentStep) . '</div>';
        }
        ?>
    <?php else: ?>
        <?php // This part is less likely to be hit if controller always defines a currentStep or redirects ?>
        <div class="alert alert-info">Wizard has ended or step is not defined.</div>
        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success">
                <?= Yii::$app->session->getFlash('success') ?>
            </div>
        <?php endif; ?>
        <?php // Link to view or index could be here if wizard completes without redirect by controller ?>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger">
                <?= Yii::$app->session->getFlash('error') ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>
