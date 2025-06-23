<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicant $model */

$this->title = 'Update Applicant: ' . $model->applicant_id;
$this->params['breadcrumbs'][] = ['label' => 'Applicants', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->applicant_id, 'url' => ['view', 'applicant_id' => $model->applicant_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-applicant-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>