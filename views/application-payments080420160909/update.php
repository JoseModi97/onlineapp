<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationPayments080420160909 $model */

$this->title = 'Update Application Payments080420160909: ' . $model->payment_id;
$this->params['breadcrumbs'][] = ['label' => 'Application Payments080420160909', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->payment_id, 'url' => ['view', 'payment_id' => $model->payment_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-application-payments080420160909-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>