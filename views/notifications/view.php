<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppNotifications $model */

$this->title = $model->notification_id;
$this->params['breadcrumbs'][] = ['label' => 'Notifications', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-notifications-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'notification_id' => $model->notification_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'notification_id' => $model->notification_id], [
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
            'notification_id',
            'applicant_id',
            'application_ref_no',
            'notification_type',
            'recipient',
            'sender',
            'subject',
            'message:ntext',
            'date_added',
            'date_sent',
            'sent_status',
            'message_read',
            'user_deleted',
        ],
    ]) ?>

</div>