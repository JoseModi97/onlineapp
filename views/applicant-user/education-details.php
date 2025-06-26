<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\AppEducationSystem; // For dropdown
use yii\helpers\ArrayHelper; // For dropdown

/** @var yii\web\View $this */
/** @var app\models\AppApplicantEducation $educationModel */
/** @var yii\widgets\ActiveForm $form */
/** @var string $currentStepForView Specific to know which step is being rendered, if needed */

// In a real scenario, you might want to pass this from the controller,
// or have a more robust way to get this list if it's extensive.
$educationSystems = AppEducationSystem::find()->orderBy('edu_system_name')->all();
$educationSystemItems = ArrayHelper::map($educationSystems, 'edu_system_code', 'edu_system_name');

// For 'relevant' field, if it has predefined values, e.g. Yes/No
$relevantItems = [
    'Yes' => 'Yes',
    'No' => 'No',
];
// For 'cert_source', assuming it's an integer code with known meanings
// Example: 1 for 'Original Seen', 2 for 'Copy Certified', etc.
// These should ideally come from a model or constants if they are fixed.
$certSourceItems = [
    1 => 'Original Document Seen',
    2 => 'Certified True Copy',
    3 => 'Online Verification',
    // Add other sources as needed
];

?>

<div class="app-applicant-education-form">

    <?php $form = ActiveForm::begin([
        'id' => 'education-details-form',
        // Ensure options for AJAX validation if you've set it up in controller/model
        // 'enableAjaxValidation' => true,
        // 'validationUrl' => Url::to(['/applicant-user/validate-step', 'step' => $currentStepForView]),
        'options' => ['enctype' => 'multipart/form-data'] // IMPORTANT for file uploads
    ]); ?>

    <?= $form->field($educationModel, 'edu_system_code')->dropDownList($educationSystemItems, ['prompt' => 'Select Education System']) ?>

    <?= $form->field($educationModel, 'institution_name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($educationModel, 'edu_ref_no')->textInput(['maxlength' => true]) ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($educationModel, 'year_from')->textInput(['type' => 'number', 'min' => 1950, 'max' => date('Y')]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($educationModel, 'year_to')->textInput(['type' => 'number', 'min' => 1950, 'max' => (date('Y') + 5)]) ?>
        </div>
    </div>

    <?= $form->field($educationModel, 'grade')->textInput(['maxlength' => true]) ?>

    <?= $form->field($educationModel, 'grade_per_student')->textInput(['maxlength' => true]) ?>

    <?= $form->field($educationModel, 'points_score')->textInput(['type' => 'number']) ?>

    <?= $form->field($educationModel, 'pi_gpa')->textInput(['type' => 'number', 'step' => '0.01']) ?>

    <?= $form->field($educationModel, 'relevant')->dropDownList($relevantItems, ['prompt' => 'Is this relevant?']) ?>

    <?= $form->field($educationModel, 'name_as_per_cert')->textInput(['maxlength' => true]) ?>

    <?= $form->field($educationModel, 'remarks')->textarea(['rows' => 3]) ?>

    <?= $form->field($educationModel, 'education_certificate_file')->fileInput() ?>
    <?php if (!$educationModel->isNewRecord && $educationModel->file_name): ?>
        <div class="mb-3">
            Current file: <?= Html::encode($educationModel->file_name) ?>
            <?= Html::hiddenInput('AppApplicantEducation[file_name_hidden]', $educationModel->file_name) ?>
            <?php // You could add a link to view/download the file here if $educationModel->file_path is public ?>
        </div>
    <?php endif; ?>


    <?= $form->field($educationModel, 'cert_source')->dropDownList($certSourceItems, ['prompt' => 'Select Certificate Source']) ?>

    <?php // Hidden field for education_id if updating an existing record.
      // The controller logic for loading the model for POST processing already tries to fetch the latest,
      // but if a specific record was loaded via session and its education_id stored, this ensures it.
      // The session storage of attributesToSave['education_id'] is key.
    ?>
    <?php if (!$educationModel->isNewRecord && $educationModel->education_id): ?>
        <?= Html::activeHiddenInput($educationModel, 'education_id') ?>
    <?php endif; ?>


    <?php // Note: The main submit buttons (Next, Previous, Save) are in the update-wizard.php layout ?>

    <?php ActiveForm::end(); ?>

</div>
