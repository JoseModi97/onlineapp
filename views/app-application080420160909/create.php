<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplication080420160909 $model */

$this->title = 'Create Application080420160909';
$this->params['breadcrumbs'][] = ['label' => 'App Application080420160909s', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application080420160909-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>