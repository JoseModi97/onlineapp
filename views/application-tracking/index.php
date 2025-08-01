<?php

use app\models\AppApplicationTracking;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationTrackingSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'App Application Trackings';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-tracking-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create App Application Tracking', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'export' => [
            'fontAwesome' => true
        ],
        'exportConfig' => [
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'application-tracking-'.date('Y-m-d')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'application-tracking-'.date('Y-m-d')],
            GridView::EXCEL => ['label' => 'Export as EXCEL', 'filename' => 'application-tracking-'.date('Y-m-d')],
            GridView::TEXT => ['label' => 'Export as TEXT', 'filename' => 'application-tracking-'.date('Y-m-d')],
            GridView::JSON => ['label' => 'Export as JSON', 'filename' => 'application-tracking-'.date('Y-m-d')],
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'tracking_id',
            'application_id',
            'status_id',
            'remarks',
            'audit_date',
            //'user_id',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicationTracking $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'tracking_id' => $model->tracking_id]);
                }
            ],
        ],
    ]); ?>


</div>