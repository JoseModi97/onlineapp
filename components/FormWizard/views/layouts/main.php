<?php
/**
 * @var \yii\web\View $this (if in Yii context)
 * @var string $content The content of the current wizard step.
 * @var \app\components\FormWizard\WizardController $wizardController
 * @var string $currentStepKey
 * @var array $stepConfig
 * @var array $allStepsConfig
 * @var string $navigationHtml
 * @var string $buttonsHtml
 * @var array $errors
 */

use yii\helpers\Html; // Assuming Yii context for Html helper, otherwise use plain HTML

// $this->title = $stepConfig['title'] ?? 'Form Wizard'; // Example for Yii view title
?>
<div class="form-wizard-container" id="<?= Html::encode($wizardController->config['wizardId']) ?>-container" data-wizard-id="<?= Html::encode($wizardController->config['wizardId']) ?>">
    <h1><?= Html::encode($stepConfig['title'] ?? 'Form Wizard') ?></h1>

    <?php if (isset($navigationHtml)): ?>
        <div class="wizard-navigation-container">
            <?= $navigationHtml ?>
        </div>
    <?php endif; ?>

    <div id="wizard-general-message-area" class="mb-3">
        <?php
        // Display general messages passed from controller (e.g. after save/cancel)
        // This part might be handled by JavaScript updating a specific div for AJAX responses.
        // For non-AJAX, flash messages or direct messages can be shown here.
        if (!empty($wizardController->config['lastResponse']['message'])) {
            $messageType = $wizardController->config['lastResponse']['success'] ? 'success' : 'danger';
            echo "<div class='alert alert-{$messageType}'>" . Html::encode($wizardController->config['lastResponse']['message']) . "</div>";
        }
        ?>
    </div>


    <form id="<?= Html::encode($wizardController->config['wizardId']) ?>-form" method="POST" action="">
        <?php
            // CSRF token for Yii forms. If not Yii, this needs to be adapted.
            // echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken);
        ?>
        <input type="hidden" name="current_step_key" value="<?= Html::encode($currentStepKey) ?>" />
        <input type="hidden" name="wizard_id" value="<?= Html::encode($wizardController->config['wizardId']) ?>" />

        <div class="wizard-step-content" id="<?= Html::encode($wizardController->config['wizardId']) ?>-step-content">
            <?= $content ?? '<p>Step content goes here.</p>' ?>
        </div>

        <?php if (isset($buttonsHtml)): ?>
            <div class="wizard-buttons-container">
                <?= $buttonsHtml ?>
            </div>
        <?php endif; ?>
    </form>

    <?php
    // Register JS for the wizard if ajaxEnabled.
    // This would typically be done in an AssetBundle in Yii.
    // For now, a placeholder.
    if ($wizardController->config['ajaxEnabled']) {
        // $this->registerJsFile('@web/js/generic-wizard-ajax.js', ['depends' => [\yii\web\JqueryAsset::class]]);
        // $jsConfig = json_encode(['wizardId' => $wizardController->config['wizardId'], 'initialStep' => $currentStepKey, ...]);
        // $this->registerJs("GenericWizard.init({$jsConfig});");
    }
    ?>
</div>
