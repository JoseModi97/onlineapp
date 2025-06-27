<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicant $model */

$this->title = 'Create Applicant';
$this->params['breadcrumbs'][] = ['label' => 'Applicants', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>