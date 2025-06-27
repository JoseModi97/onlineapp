<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationFees $model */

$this->title = $model->application_fee_id;
$this->params['breadcrumbs'][] = ['label' => 'App Application Fees', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-application-fees-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'application_fee_id' => $model->application_fee_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'application_fee_id' => $model->application_fee_id], [
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
            'application_fee_id',
            'programme_type',
            'amount',
            'currency',
            'status:boolean',
            'date_added',
        ],
    ]) ?>

</div>
