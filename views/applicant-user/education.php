<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\AppEducationSystem; // Assuming this model exists for dropdown
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantEducation $educationModel */
/** @var app\models\AppApplicant $appApplicantModel */
/** @var yii\widgets\ActiveForm $form */
/** @var string $currentStepForView */

// If $educationModel is null (e.g. applicant_id not yet available), initialize it.
// The controller should ideally always pass an initialized model.
if ($educationModel === null) {
    // If $appApplicantModel is available and has an ID, we can use it.
    // Otherwise, it's a new AppApplicantEducation without an applicant_id yet.
    $educationModel = new \app\models\AppApplicantEducation();
    if ($appApplicantModel && $appApplicantModel->applicant_id) {
        $educationModel->applicant_id = $appApplicantModel->applicant_id;
    }
    // It's possible $currentStepForView might not be 'education' if this partial is misused.
    // However, in the wizard context, it should be.
    Yii::warning("Education step view received a null \$educationModel. Initialized a new one. Current step: " . ($currentStepForView ?? 'unknown'));
}

// Check if AppEducationSystem model exists and can be used for a dropdown
$educationSystems = [];
if (class_exists('app\models\AppEducationSystem')) {
    try {
        $educationSystems = ArrayHelper::map(AppEducationSystem::find()->orderBy('edu_system_name')->asArray()->all(), 'edu_system_code', 'edu_system_name');
    } catch (\Exception $e) {
        Yii::error("Failed to load education systems: " . $e->getMessage());
        // Provide a fallback or handle error appropriately
        $educationSystems = [0 => 'Error loading systems - Enter Manually'];
    }
} else {
    $educationSystems = [0 => 'Education System Model not found - Enter Manually'];
}


?>

<div class="app-applicant-education-form">

    <?php $form = ActiveForm::begin([
        'id' => 'education-step-form', // Unique ID for the form
        // No action needed here, AJAX handles submission via wizard buttons
    ]); ?>

    <?= Html::hiddenInput('current_step_validated', $currentStepForView) ?>
    <?php if ($educationModel->applicant_id): ?>
        <?= $form->field($educationModel, 'applicant_id')->hiddenInput()->label(false) ?>
    <?php else: ?>
        <?php // If applicant_id is not set, it means the AppApplicant model hasn't been saved yet.
              // The controller handles associating it upon saving AppApplicant.
              // We might show a message or just let it be, as it's a backend concern.
        ?>
        <!-- <div class="alert alert-warning">Applicant ID is not yet set. It will be assigned upon saving previous steps.</div> -->
    <?php endif; ?>

    <?php
    // Assuming education_id might be relevant if updating an existing record,
    // though the current wizard logic seems to imply one primary education record.
    // If $educationModel is not new, its PK (education_id) should be submitted.
    if (!$educationModel->isNewRecord && $educationModel->education_id) {
        echo $form->field($educationModel, 'education_id')->hiddenInput()->label(false);
    }
    ?>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($educationModel, 'edu_system_code')->dropDownList($educationSystems, ['prompt' => 'Select Education System']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($educationModel, 'institution_name')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($educationModel, 'edu_ref_no')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($educationModel, 'name_as_per_cert')->textInput(['maxlength' => true]) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <?= $form->field($educationModel, 'year_from')->textInput(['type' => 'number', 'maxlength' => 4]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($educationModel, 'year_to')->textInput(['type' => 'number', 'maxlength' => 4]) ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($educationModel, 'grade')->textInput(['maxlength' => true]) ?>
        </div>
         <div class="col-md-3">
            <?= $form->field($educationModel, 'cert_source')->textInput(['type' => 'number']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($educationModel, 'points_score')->textInput(['type' => 'number']) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($educationModel, 'remarks')->textarea(['rows' => 3]) ?>
        </div>
    </div>

    <?php // Add other fields as necessary based on AppApplicantEducation model
    // e.g., 'grade_per_student', 'pi_gpa', 'relevant', 'file_path', 'file_name'
    // For file uploads, more complex handling would be needed, similar to profile_image in account-settings.
    // For simplicity, file fields are omitted here but can be added if required.
    ?>
    <?php /*
    <?= $form->field($educationModel, 'grade_per_student')->textInput(['maxlength' => true]) ?>
    <?= $form->field($educationModel, 'pi_gpa')->textInput(['type' => 'number']) ?>
    <?= $form->field($educationModel, 'relevant')->dropDownList(['Yes' => 'Yes', 'No' => 'No'], ['prompt' => '']) ?>
    <?= $form->field($educationModel, 'file_path')->textInput(['maxlength' => true]) ?> // Likely a file input
    <?= $form->field($educationModel, 'file_name')->textInput(['maxlength' => true]) ?>
    */ ?>


    <?php ActiveForm::end(); ?>

</div>

<?php
// Add any specific JavaScript for this step if needed
// For example, date pickers, conditional logic, etc.
// $this->registerJs("
// // Custom JS for education step
// console.log('Education step loaded');
// ");
?>
