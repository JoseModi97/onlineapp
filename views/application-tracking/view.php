<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationTracking $model */

$this->title = $model->tracking_id;
$this->params['breadcrumbs'][] = ['label' => 'App Application Trackings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-application-tracking-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'tracking_id' => $model->tracking_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'tracking_id' => $model->tracking_id], [
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
            'tracking_id',
            'application_id',
            'status_id',
            'remarks',
            'audit_date',
            'user_id',
        ],
    ]) ?>

</div>
