<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantEducation $model */

$this->title = $model->education_id;
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Educations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-applicant-education-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'education_id' => $model->education_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'education_id' => $model->education_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'education_id',
            'applicant_id',
            'edu_system_code',
            'institution_name',
            'edu_ref_no',
            'year_from',
            'year_to',
            'grade',
            'grade_per_student',
            'points_score',
            'pi_gpa',
            'relevant',
            'remarks',
            'name_as_per_cert',
            'file_path',
            'file_name',
            'cert_source',
        ],
    ]) ?>

</div>