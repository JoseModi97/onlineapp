<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

<?php
// Ensure this view receives $model directly, not via $event
?>
/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="personal-details-form">

    <?php $form = ActiveForm::begin([
        'id' => 'personal-details-form',
        // Action will be handled by the main update-wizard URL, parameters define step
    ]); ?>

    <?= $form->field($model, 'surname')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'other_name')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'email_address')->textInput(['maxlength' => true]) ?>
    <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true]) ?>

    <div class="form-group">
        <?= Html::submitButton('Next <i class="fas fa-arrow-right"></i>', ['class' => 'btn btn-primary', 'name' => 'wizard_next']) ?>
        <?= Html::submitButton('<i class="fas fa-times"></i> Cancel', ['class' => 'btn btn-secondary', 'name' => 'wizard_cancel', 'formnovalidate' => true]) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
