<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantEducation $model */

$this->title = 'Create App Applicant Education';
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Educations', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-education-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
