<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantWorkExp $model */

$this->title = 'Update Applicant Work Exp: ' . $model->experience_id;
$this->params['breadcrumbs'][] = ['label' => 'Applicant Work Exps', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->experience_id, 'url' => ['view', 'experience_id' => $model->experience_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-applicant-work-exp-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>