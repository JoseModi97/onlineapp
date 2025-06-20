<?php

use app\models\AppNotifications;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\NotificationsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Notifications';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-notifications-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Notifications', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'notification_id',
            'applicant_id',
            'application_ref_no',
            'notification_type',
            'recipient',
            //'sender',
            //'subject',
            //'message:ntext',
            //'date_added',
            //'date_sent',
            //'sent_status',
            //'message_read',
            //'user_deleted',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppNotifications $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'notification_id' => $model->notification_id]);
                }
            ],
        ],
    ]); ?>


</div>