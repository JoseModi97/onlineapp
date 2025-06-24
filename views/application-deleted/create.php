<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\AppApplicationDeleted $model */

$this->title = 'Create Application Deleted Record';
$this->params['breadcrumbs'][] = ['label' => 'Application Deleted Records', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-application-deleted-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>