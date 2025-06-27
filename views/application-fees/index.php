<?php

use app\models\AppApplicationFees;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationFeesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'App Application Fees';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-fees-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create App Application Fees', ['create'], ['class' => 'btn btn-success']) ?>
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
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'application-fees-'.date('Y-m-d')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'application-fees-'.date('Y-m-d')],
            GridView::EXCEL => ['label' => 'Export as EXCEL', 'filename' => 'application-fees-'.date('Y-m-d')],
            GridView::TEXT => ['label' => 'Export as TEXT', 'filename' => 'application-fees-'.date('Y-m-d')],
            GridView::JSON => ['label' => 'Export as JSON', 'filename' => 'application-fees-'.date('Y-m-d')],
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'application_fee_id',
            'programme_type',
            'amount',
            'currency',
            'status:boolean',
            //'date_added',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicationFees $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'application_fee_id' => $model->application_fee_id]);
                }
            ],
        ],
    ]); ?>


</div>