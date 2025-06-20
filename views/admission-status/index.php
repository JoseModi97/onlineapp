<?php

use app\models\AppAdmissionStatus;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\AdmissionStatusSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Admission Statuses';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-admission-status-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Admission Status', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            // 'status_id',
            'status_name',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppAdmissionStatus $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'status_id' => $model->status_id]);
                }
            ],
        ],
    ]); ?>


</div>