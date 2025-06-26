<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationIntake $model */

$this->title = 'Update Application Intake: ' . $model->intake_code;
$this->params['breadcrumbs'][] = ['label' => 'Application Intakes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->intake_code, 'url' => ['view', 'intake_code' => $model->intake_code]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-application-intake-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>