<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplication $model */

$this->title = 'Update App Application: ' . $model->application_id;
$this->params['breadcrumbs'][] = ['label' => 'App Applications', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->application_id, 'url' => ['view', 'application_id' => $model->application_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-application-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
