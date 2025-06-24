<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\AppApplicant $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="app-applicant-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'applicant_user_id')->widget(Select2::classname(), [
        'initValueText' => ($model->applicant_user_id && $model->applicantUser) ? $model->applicantUser->first_name . ' ' . $model->applicantUser->last_name . ' (' . $model->applicantUser->email_address . ')' : '',
        'options' => ['placeholder' => 'Search for a user ...'],
        'pluginOptions' => [
            'allowClear' => true,
            'minimumInputLength' => 1,
            'ajax' => [
                'url' => Url::to(['applicant-user/user-list']),
                'dataType' => 'json',
                'data' => new \yii\web\JsExpression('function(params) { return {q:params.term}; }')
            ],
            'escapeMarkup' => new \yii\web\JsExpression('function (markup) { return markup; }'),
            'templateResult' => new \yii\web\JsExpression('function(user) { return user.text; }'),
            'templateSelection' => new \yii\web\JsExpression('function(user) { return user.text; }'),
        ],
    ]); ?>

    <?= $form->field($model, 'gender')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'dob')->textInput() ?>

    <?= $form->field($model, 'religion')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'country_code')->textInput() ?>

    <?= $form->field($model, 'national_id')->textInput() ?>

    <?= $form->field($model, 'marital_status')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
