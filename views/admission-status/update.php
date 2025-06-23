<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppAdmissionStatus $model */

$this->title = 'Update Admission Status: ' . $model->status_id;
$this->params['breadcrumbs'][] = ['label' => 'Admission Statuses', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->status_id, 'url' => ['view', 'status_id' => $model->status_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-admission-status-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>