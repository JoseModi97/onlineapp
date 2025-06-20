<?php

use app\models\AppApplicant;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicantSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Applicants';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Applicant', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'applicant_id',
            'applicant_user_id',
            'gender',
            'dob',
            'religion',
            //'country_code',
            //'national_id',
            //'marital_status',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicant $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'applicant_id' => $model->applicant_id]);
                }
            ],
        ],
    ]); ?>


</div>