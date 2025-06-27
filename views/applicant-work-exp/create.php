<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantWorkExp $model */

$this->title = 'Create Applicant Work Exp';
$this->params['breadcrumbs'][] = ['label' => 'Applicant Work Exps', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-work-exp-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>