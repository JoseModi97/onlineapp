<?php

use app\models\AppApplicantEducation;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicantEducationSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Applicant Educations';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-education-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Applicant Education', ['create'], ['class' => 'btn btn-success']) ?>
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
            GridView::CSV => ['label' => 'Export as CSV', 'filename' => 'applicant-education-'.date('Y-m-d')],
            GridView::HTML => ['label' => 'Export as HTML', 'filename' => 'applicant-education-'.date('Y-m-d')],
            GridView::EXCEL => ['label' => 'Export as EXCEL', 'filename' => 'applicant-education-'.date('Y-m-d')],
            GridView::TEXT => ['label' => 'Export as TEXT', 'filename' => 'applicant-education-'.date('Y-m-d')],
            GridView::JSON => ['label' => 'Export as JSON', 'filename' => 'applicant-education-'.date('Y-m-d')],
        ],
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'education_id',
            'applicant_id',
            'edu_system_code',
            'institution_name',
            'edu_ref_no',
            //'year_from',
            //'year_to',
            //'grade',
            //'grade_per_student',
            //'points_score',
            //'pi_gpa',
            //'relevant',
            //'remarks',
            //'name_as_per_cert',
            //'file_path',
            //'file_name',
            //'cert_source',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicantEducation $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'education_id' => $model->education_id]);
                }
            ],
        ],
    ]); ?>


</div>