<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationFees $model */

$this->title = 'Update App Application Fees: ' . $model->application_fee_id;
$this->params['breadcrumbs'][] = ['label' => 'App Application Fees', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->application_fee_id, 'url' => ['view', 'application_fee_id' => $model->application_fee_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-application-fees-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
