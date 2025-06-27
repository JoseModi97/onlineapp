<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppNotifications $model */

$this->title = 'Update Notifications: ' . $model->notification_id;
$this->params['breadcrumbs'][] = ['label' => 'Notifications', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->notification_id, 'url' => ['view', 'notification_id' => $model->notification_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-notifications-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>