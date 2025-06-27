<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppAdmissionStatus $model */

$this->title = $model->status_id;
$this->params['breadcrumbs'][] = ['label' => 'Admission Statuses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-admission-status-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'status_id' => $model->status_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'status_id' => $model->status_id], [
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
            'status_id',
            'status_name',
        ],
    ]) ?>

</div>