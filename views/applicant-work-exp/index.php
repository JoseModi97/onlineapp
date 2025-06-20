<?php

use app\models\AppApplicantWorkExp;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicantWorkExpSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Applicant Work Exps';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-work-exp-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Applicant Work Exp', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'experience_id',
            'applicant_id',
            'employer_name',
            'designation',
            'year_from',
            //'year_to',
            //'assignment',
            //'relevant',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicantWorkExp $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'experience_id' => $model->experience_id]);
                }
            ],
        ],
    ]); ?>


</div>