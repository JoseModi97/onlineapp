<?php

use app\models\AppApplicationPayments080420160909;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationPayments080420160909Search $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Application Payments080420160909';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-payments080420160909-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Application Payments080420160909', ['create'], ['class' => 'btn btn-success']) ?>
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
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'application-payments-'.date('Y-m-d')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'application-payments-'.date('Y-m-d')],
            GridView::EXCEL => ['label' => 'Export as EXCEL', 'filename' => 'application-payments-'.date('Y-m-d')],
            GridView::TEXT => ['label' => 'Export as TEXT', 'filename' => 'application-payments-'.date('Y-m-d')],
            GridView::JSON => ['label' => 'Export as JSON', 'filename' => 'application-payments-'.date('Y-m-d')],
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'payment_id',
            'application_id',
            'transaction_id',
            'receipt_no',
            'amount_paid',
            //'currency_code',
            //'payment_channel',
            //'transaction_ref',
            //'payment_ref',
            //'processing_date',
            //'sync_status',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicationPayments080420160909 $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'payment_id' => $model->payment_id]);
                }
            ],
        ],
    ]); ?>


</div>