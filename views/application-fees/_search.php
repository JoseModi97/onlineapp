<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicationFeesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-application-fees-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'application_fee_id') ?>

    <?= $form->field($model, 'programme_type') ?>

    <?= $form->field($model, 'amount') ?>

    <?= $form->field($model, 'currency') ?>

    <?= $form->field($model, 'status')->checkbox() ?>

    <?php // echo $form->field($model, 'date_added') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
