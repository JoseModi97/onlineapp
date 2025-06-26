<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationPayments080420160909 $model */

$this->title = 'Create Application Payments080420160909';
$this->params['breadcrumbs'][] = ['label' => 'Application Payments080420160909', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-payments080420160909-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>