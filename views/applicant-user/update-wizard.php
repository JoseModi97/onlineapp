<?php

use yii\helpers\Html;
use beastbytes\wizard\WizardMenu;

/** @var yii\web\View $this */
/** @var beastbytes\wizard\WizardEvent $event */

$this->title = 'Update Applicant Wizard';
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];
if ($event->data['model'] && !$event->data['model']->isNewRecord) {
    $this->params['breadcrumbs'][] = ['label' => $event->data['model']->applicant_user_id, 'url' => ['view', 'applicant_user_id' => $event->data['model']->applicant_user_id]];
}
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-user-update-wizard">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= WizardMenu::widget(['step' => $event->step, 'wizard' => $event->sender]) ?>

    <?php if (isset($event->data['message'])): ?>
        <div class="alert alert-danger"><?= Html::encode($event->data['message']) ?></div>
    <?php endif; ?>

    <?= $this->render($event->step, ['event' => $event]) ?>

</div>
