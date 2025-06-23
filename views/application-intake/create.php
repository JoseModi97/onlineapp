<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationIntake $model */

$this->title = 'Create Application Intake';
$this->params['breadcrumbs'][] = ['label' => 'Application Intakes', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-intake-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>