<?php

/** @var \yii\web\View $this */
/** @var string $content */
/** @var \app\components\FormWizard\WizardController $wizardController (passed by GenericWizardController's renderStep) */
/** @var string $currentStepKey */
/** @var array $stepConfig */

use app\assets\AppAsset; // Or your relevant asset bundle
use app\components\FormWizard\assets\FormWizardAsset; // Import the new AssetBundle
use yii\helpers\Html;
use yii\helpers\Url;

AppAsset::register($this); // Register your application assets
FormWizardAsset::register($this); // Register the FormWizard assets

// Title for the page can be set based on the wizard or step
$this->title = $wizardController->config['steps'][$currentStepKey]['title'] ?? 'Applicant Update Wizard';

// Breadcrumbs - adapted from old update-wizard.php
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];

// Try to get applicant_user_id for breadcrumbs
$applicantUserIdForBreadcrumb = null;
if (isset($wizardController->wizardData['applicant_user_id_holder'])) {
    $applicantUserIdForBreadcrumb = $wizardController->wizardData['applicant_user_id_holder'];
} elseif (isset($wizardController->wizardData['personal-details']['applicant_user_id'])) {
    $applicantUserIdForBreadcrumb = $wizardController->wizardData['personal-details']['applicant_user_id'];
} elseif (Yii::$app->request->get('applicant_user_id')) {
    $applicantUserIdForBreadcrumb = Yii::$app->request->get('applicant_user_id');
}


if ($applicantUserIdForBreadcrumb) {
    $this->params['breadcrumbs'][] = ['label' => $applicantUserIdForBreadcrumb, 'url' => ['view', 'applicant_user_id' => $applicantUserIdForBreadcrumb]];
}
$this->params['breadcrumbs'][] = $this->title;

// Configuration for the GenericWizard JS object
$wizardJsConfig = [
    'wizardId' => $wizardController->config['wizardId'],
    'initialStep' => $currentStepKey,
    // Construct the base URL for AJAX requests.
    // The GenericWizard JS will append query parameters like 'requested_step_key' and 'wizard_id'.
    'ajaxUrl' => Url::to(['applicant-user/update-wizard', 'applicant_user_id' => Yii::$app->request->get('applicant_user_id')]),
    // Add any other necessary JS configurations here
];
$this->registerJs("
    $(document).ready(function() {
        GenericWizard.init(" . json_encode($wizardJsConfig) . ");
    });
", \yii\web\View::POS_END);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <style>
        /* Basic styling for loading state - can be improved */
        .wizard-loading .wizard-buttons-container button,
        .wizard-loading .wizard-navigation-container a {
            pointer-events: none;
            opacity: 0.7;
        }
        .wizard-loading::after {
            content: 'Loading...';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255,255,255,0.8);
            padding: 20px;
            border-radius: 5px;
            z-index: 10000;
        }
    </style>
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<main role="main" class="flex-shrink-0">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= \yii\widgets\Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>

        <?= \app\widgets\Alert::widget() ?>

        <?php
        // $content here is the fully rendered output from the generic wizard's own layout
        // (components/FormWizard/views/layouts/main.php), which includes navigation,
        // the specific step's view, and buttons.
        ?>
        <?= $content ?>

    </div>
</main>

<footer class="footer mt-auto py-3 text-muted">
    <div class="container">
        <p class="float-start">&copy; My Company <?= date('Y') ?></p>
        <p class="float-end"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
