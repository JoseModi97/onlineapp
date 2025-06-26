<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantEducation $model */

$this->title = 'Update App Applicant Education: ' . $model->education_id;
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Educations', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->education_id, 'url' => ['view', 'education_id' => $model->education_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-applicant-education-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
