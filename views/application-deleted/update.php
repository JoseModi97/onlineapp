<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationDeleted $model */

$this->title = 'Update Application Deleted Record: ' . $model->application_id;
$this->params['breadcrumbs'][] = ['label' => 'Application Deleted Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->application_id, 'url' => ['view', 'application_id' => $model->application_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-application-deleted-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>