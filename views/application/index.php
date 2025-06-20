<?php

use app\models\AppApplication;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'App Applications';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create App Application', ['create'], ['class' => 'btn btn-success']) ?>
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
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'application-'.date('Y-m-d')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'application-'.date('Y-m-d')],
            GridView::EXCEL => ['label' => 'Export as EXCEL', 'filename' => 'application-'.date('Y-m-d')],
            GridView::TEXT => ['label' => 'Export as TEXT', 'filename' => 'application-'.date('Y-m-d')],
            GridView::JSON => ['label' => 'Export as JSON', 'filename' => 'application-'.date('Y-m-d')],
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'application_id',
            'applicant_id',
            'intake_code',
            'study_center_code',
            'application_ref_no',
            //'application_date',
            //'offer_accepted',
            //'final_status',
            //'application_fee_id',
            //'payment_status',
            //'processing_date',
            //'phd_proposal',
            //'application_form',
            //'sync_status',
            //'app_publish',
            //'letter_downloaded',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplication $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'application_id' => $model->application_id]);
                }
            ],
        ],
    ]); ?>


</div>