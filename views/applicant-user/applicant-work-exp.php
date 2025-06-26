<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; // Using Bootstrap 5 ActiveForm for consistency

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model User model (though not directly used for form fields here) */
/** @var app\models\AppApplicant $appApplicantModel Applicant model (not directly used for form fields here) */
/** @var app\models\AppApplicantWorkExp $workExpModel Work Experience model */
/** @var array $stepData Custom data for the step (e.g., messages) */
/** @var string $currentStepForView The key of the current step being rendered */

// $this->title = 'Work Experience'; // Title can be set in the main wizard view or here
?>

<div class="applicant-work-exp-form">

    <?php if (isset($stepData['message']) && !empty($stepData['message'])): ?>
        <div class="alert alert-danger"><?= Html::encode($stepData['message']) ?></div>
    <?php endif; ?>

    <?php $form = ActiveForm::begin([
        'id' => 'work-experience-step-form', // Unique ID for the form
        // AJAX validation can be enabled if configured in controller and model
        // 'enableAjaxValidation' => true,
        // 'validateOnChange' => true,
        // 'validateOnBlur' => true,
    ]); ?>

    <?= $form->field($workExpModel, 'employer_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($workExpModel, 'designation')->textInput(['maxlength' => true]) ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($workExpModel, 'year_from')->textInput(['type' => 'date']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($workExpModel, 'year_to')->textInput(['type' => 'date']) ?>
        </div>
    </div>

    <?= $form->field($workExpModel, 'assignment')->textarea(['rows' => 4]) ?>

    <?= $form->field($workExpModel, 'relevant')->dropDownList(
        ['Yes' => 'Yes', 'No' => 'No'],
        ['prompt' => 'Is this experience relevant?']
    ) ?>
    <?php // Alternatively, use a checkbox for boolean 'relevant' if DB field is boolean/tinyint ?>
    <?php // echo $form->field($workExpModel, 'relevant')->checkbox() ?>


    <?php // Hidden input to identify the current step being submitted, handled by JS now
    // echo Html::hiddenInput('current_step_validated', $currentStepForView); ?>

    <div class="form-group mt-3">
        <?= Html::button('Skip this step', ['class' => 'btn btn-link', 'id' => 'wizard-skip-work-exp-btn']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// Add any specific JavaScript for this step if needed
// For example, initializing date pickers if not using native HTML5 date type
$this->registerJs("
    // console.log('Work Experience step view loaded.');
    // Add any step-specific JS initialization here.
", \yii\web\View::POS_READY, 'work-exp-step-js');
?>
