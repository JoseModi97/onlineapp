<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationWithoutRef $model */

$this->title = 'Create Application Without Ref';
$this->params['breadcrumbs'][] = ['label' => 'Application Without Refs', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-without-ref-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>