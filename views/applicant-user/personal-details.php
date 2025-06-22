<?php

use yii\helpers\Html;
// Removed: use yii\widgets\ActiveForm; // ActiveForm is now started in update-wizard.php

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser|null $model Nullable if not the active step for this model */
/** @var yii\widgets\ActiveForm $form Passed from update-wizard.php */
?>

<div class="personal-details-form">

    <?php // ActiveForm::begin() is removed. The form is started in update-wizard.php ?>

    <?php if ($model): // Check if model is provided for this step ?>
        <?= $form->field($model, 'surname')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'first_name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'other_name')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'email_address')->textInput(['maxlength' => true]) ?>
        <?= $form->field($model, 'mobile_no')->textInput(['maxlength' => true]) ?>
    <?php else: ?>
        <p>Personal details form is not available at this step.</p>
    <?php endif; ?>

    <?php // Submit buttons are removed. They are now in update-wizard.php ?>

    <?php // ActiveForm::end() is removed. The form is ended in update-wizard.php ?>

</div>