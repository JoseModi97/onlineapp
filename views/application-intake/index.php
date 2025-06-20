<?php

use app\models\AppApplicationIntake;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationIntakeSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Application Intakes';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-intake-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Application Intake', ['create'], ['class' => 'btn btn-success']) ?>
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
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'application-intake-'.date('Y-m-d')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'application-intake-'.date('Y-m-d')],
            GridView::EXCEL => ['label' => 'Export as EXCEL', 'filename' => 'application-intake-'.date('Y-m-d')],
            GridView::TEXT => ['label' => 'Export as TEXT', 'filename' => 'application-intake-'.date('Y-m-d')],
            GridView::JSON => ['label' => 'Export as JSON', 'filename' => 'application-intake-'.date('Y-m-d')],
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'intake_code',
            'intake_name',
            'academic_year',
            'degree_code',
            'application_deadline',
            //'reporting_date',
            //'start_date',
            //'end_date',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicationIntake $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'intake_code' => $model->intake_code]);
                }
            ],
        ],
    ]); ?>


</div>