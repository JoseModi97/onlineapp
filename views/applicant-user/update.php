<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */

$this->title = 'Update App Applicant User: ' . $model->applicant_user_id;
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->applicant_user_id, 'url' => ['view', 'applicant_user_id' => $model->applicant_user_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-applicant-user-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
