<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppContactTypes $model */

$this->title = 'Update Contact Types: ' . $model->contact_type_id;
$this->params['breadcrumbs'][] = ['label' => 'Contact Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->contact_type_id, 'url' => ['view', 'contact_type_id' => $model->contact_type_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="app-contact-types-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>