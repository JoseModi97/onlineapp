<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */

$this->title = 'Create App Applicant User';
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-user-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
