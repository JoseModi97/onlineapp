<?php

use app\models\AppApplicationDeleted;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationDeletedSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Application Deleted Records';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-deleted-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Application Deleted Record', ['create'], ['class' => 'btn btn-success']) ?>
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
                'urlCreator' => function ($action, AppApplicationDeleted $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'application_id' => $model->application_id]);
                }
            ],
        ],
    ]); ?>


</div>