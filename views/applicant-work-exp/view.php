<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantWorkExp $model */

$this->title = $model->experience_id;
$this->params['breadcrumbs'][] = ['label' => 'Applicant Work Exps', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-applicant-work-exp-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'experience_id' => $model->experience_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'experience_id' => $model->experience_id], [
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
            'experience_id',
            'applicant_id',
            'employer_name',
            'designation',
            'year_from',
            'year_to',
            'assignment',
            'relevant',
        ],
    ]) ?>

</div>