<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplication080420160909 $model */

$this->title = 'Update Application080420160909: ' . $model->application_id;
$this->params['breadcrumbs'][] = ['label' => 'App Application080420160909s', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->application_id, 'url' => ['view', 'application_id' => $model->application_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-application080420160909-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>