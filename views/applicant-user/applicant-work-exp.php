<?php

use app\models\AppApplicant;
use app\models\AppApplicantWorkExp;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; // Using Bootstrap 5 ActiveForm for consistency

/** @var yii\web\View $this */
/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model User model */
/** @var app\models\AppApplicant $appApplicantModel Applicant model */
/** @var app\models\AppApplicantWorkExp $workExpModel Work Experience model for the new entry form */
/** @var array $stepData Custom data for the step (e.g., messages from controller) */
/** @var string $currentStepForView The key of the current step being rendered */
/** @var array|null $existingWorkExperiences Array of past work experiences, passed from controller (could be null if none or not applicable) */

// The lines fetching AppApplicant and AppApplicantWorkExp directly in the view have been removed.
// This data is now expected to be passed from the ApplicantUserController::actionUpdateWizard()
// into the $existingWorkExperiences variable.
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
    <?php // Alternatively, use a checkbox for boolean 'relevant' if DB field is boolean/tinyint 
    ?>
    <?php // echo $form->field($workExpModel, 'relevant')->checkbox() 
    ?>


    <?php // Hidden input to identify the current step being submitted, handled by JS now
    // echo Html::hiddenInput('current_step_validated', $currentStepForView); 
    ?>

    <?php ActiveForm::end(); ?>

</div>

<hr class="my-4">

<h4>Previously Added Work Experience</h4>
<?php if (!empty($existingWorkExperiences)): ?>
    <div class="table-responsive">
        <table class="table table-striped table-bordered" id="existing-work-experience-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employer Name</th>
                    <th>Designation</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Assignment</th>
                    <th>Relevant</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($existingWorkExperiences as $index => $exp): ?>
                    <tr data-experience-id="<?= Html::encode($exp['experience_id']) ?>">
                        <td><?= $index + 1 ?></td>
                        <td data-field="employer_name"><?= Html::encode($exp['employer_name']) ?></td>
                        <td data-field="designation"><?= Html::encode($exp['designation']) ?></td>
                        <td data-field="year_from"><?= Html::encode(Yii::$app->formatter->asDate($exp['year_from'])) ?></td>
                        <td data-field="year_to"><?= Html::encode($exp['year_to'] ? Yii::$app->formatter->asDate($exp['year_to']) : 'Present') ?></td>
                        <td data-field="assignment" style="white-space: pre-wrap; word-break: break-word;"><?= Html::encode($exp['assignment']) ?></td>
                        <td data-field="relevant"><?= Html::encode($exp['relevant']) ?></td>
                        <td>
                            <button type="button" class="btn btn-sm btn-primary btn-edit-work-exp" data-id="<?= Html::encode($exp['experience_id']) ?>">Edit</button>
                            <button type="button" class="btn btn-sm btn-danger btn-delete-work-exp" data-id="<?= Html::encode($exp['experience_id']) ?>">Delete</button>
                            <button type="button" class="btn btn-sm btn-success btn-save-work-exp" data-id="<?= Html::encode($exp['experience_id']) ?>" style="display:none;">Save</button>
                            <button type="button" class="btn btn-sm btn-warning btn-cancel-work-exp" data-id="<?= Html::encode($exp['experience_id']) ?>" style="display:none;">Cancel</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>No work experience has been added yet.</p>
<?php endif; ?>

<?php
// Add any specific JavaScript for this step if needed
// For example, initializing date pickers if not using native HTML5 date type
// Or for handling the inline edit table (to be implemented in next phase)
$this->registerJs("
    // console.log('Work Experience step view loaded.');
    console.log('Existing experiences:', " . json_encode($existingWorkExperiences ?? []) . ");

    // Edit Work Experience
    $('#existing-work-experience-table').on('click', '.btn-edit-work-exp', function() {
        var experienceId = $(this).data('id');
        var editUrl = '" . \yii\helpers\Url::to(['applicant-user/get-work-experience-details']) . "?experience_id=' + experienceId;
        var \$form = $('#work-experience-step-form');

        // Store the experience_id on the form for potential update logic later
        \$form.data('editing-experience-id', experienceId);
        // Potentially change a button text or add an indicator that we are in edit mode
        // For now, just fetching and populating.

        $.ajax({
            url: editUrl,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                if(data) {
                    // Populate form fields
                    $('#appapplicantworkexp-employer_name').val(data.employer_name);
                    $('#appapplicantworkexp-designation').val(data.designation);
                    // Ensure date format is YYYY-MM-DD for date inputs
                    $('#appapplicantworkexp-year_from').val(data.year_from ? data.year_from.split(' ')[0] : ''); // Handles if datetime string is returned
                    $('#appapplicantworkexp-year_to').val(data.year_to ? data.year_to.split(' ')[0] : '');     // Handles if datetime string is returned
                    $('#appapplicantworkexp-assignment').val(data.assignment);
                    $('#appapplicantworkexp-relevant').val(data.relevant);

                    // Scroll to form for better UX
                    $('html, body').animate({
                        scrollTop: \$form.offset().top - 20 // Adjust offset as needed
                    }, 500);

                    // Optionally, change 'Add/Next' button text to 'Update Experience' or similar
                    // and handle form submission differently if editing.
                    // For now, this just populates the form. The 'Next' button will still try to add a new one
                    // unless submission logic is also updated.
                } else {
                    alert('Could not retrieve experience details.');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                var errorMessage = 'Error fetching work experience details.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage += '\\n' + jqXHR.responseJSON.message;
                } else if (errorThrown) {
                    errorMessage += '\\n' + errorThrown;
                }
                alert(errorMessage);
                console.error('AJAX Error:', textStatus, errorThrown, jqXHR.responseText);
                \$form.removeData('editing-experience-id'); // Clear editing ID on error
            }
        });
    });

    // Placeholder for future delete JS
    // $('#existing-work-experience-table').on('click', '.btn-delete-work-exp', function() { ... });

", \yii\web\View::POS_READY, 'work-exp-step-js');
?>