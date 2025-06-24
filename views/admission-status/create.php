<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppAdmissionStatus $model */

$this->title = 'Create Admission Status';
$this->params['breadcrumbs'][] = ['label' => 'Admission Statuses', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-admission-status-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>