<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantContacts $model */

$this->title = 'Update Applicant Contact: ' . $model->contact_id;
$this->params['breadcrumbs'][] = ['label' => 'Applicant Contacts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->contact_id, 'url' => ['view', 'contact_id' => $model->contact_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-applicant-contacts-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>