<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplication080420160909 $model */

$this->title = $model->application_id;
$this->params['breadcrumbs'][] = ['label' => 'App Application080420160909s', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-application080420160909-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'application_id' => $model->application_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'application_id' => $model->application_id], [
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
            'application_id',
            'applicant_id',
            'intake_code',
            'study_center_code',
            'application_ref_no',
            'application_date',
            'offer_accepted',
            'final_status',
            'application_fee_id',
            'payment_status',
            'processing_date',
            'phd_proposal',
            'application_form',
            'sync_status',
        ],
    ]) ?>

</div>
