<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationIntake $model */

$this->title = $model->intake_code;
$this->params['breadcrumbs'][] = ['label' => 'Application Intakes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-application-intake-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'intake_code' => $model->intake_code], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'intake_code' => $model->intake_code], [
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
            'intake_code',
            'intake_name',
            'academic_year',
            'degree_code',
            'application_deadline',
            'reporting_date',
            'start_date',
            'end_date',
        ],
    ]) ?>

</div>