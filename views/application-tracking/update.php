<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationTracking $model */

$this->title = 'Update App Application Tracking: ' . $model->tracking_id;
$this->params['breadcrumbs'][] = ['label' => 'App Application Trackings', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->tracking_id, 'url' => ['view', 'tracking_id' => $model->tracking_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-application-tracking-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
