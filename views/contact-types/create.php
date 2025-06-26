<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppContactTypes $model */

$this->title = 'Create Contact Types';
$this->params['breadcrumbs'][] = ['label' => 'Contact Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-contact-types-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>