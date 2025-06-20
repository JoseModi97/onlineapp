<?php

use app\models\AppApplication080420160909;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\AppApplication080420160909Search $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Application080420160909s';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application080420160909-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Application080420160909', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
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
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplication080420160909 $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'application_id' => $model->application_id]);
                }
            ],
        ],
    ]); ?>


</div>