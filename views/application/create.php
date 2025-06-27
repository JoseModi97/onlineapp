<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplication $model */

$this->title = 'Create App Application';
$this->params['breadcrumbs'][] = ['label' => 'App Applications', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
