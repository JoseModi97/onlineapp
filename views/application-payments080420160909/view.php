<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationPayments080420160909 $model */

$this->title = $model->payment_id;
$this->params['breadcrumbs'][] = ['label' => 'Application Payments080420160909', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-application-payments080420160909-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'payment_id' => $model->payment_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'payment_id' => $model->payment_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'payment_id',
            'application_id',
            'transaction_id',
            'receipt_no',
            'amount_paid',
            'currency_code',
            'payment_channel',
            'transaction_ref',
            'payment_ref',
            'processing_date',
            'sync_status',
        ],
    ]) ?>

</div>