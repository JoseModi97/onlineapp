<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; // Using Bootstrap 5 ActiveForm for consistency

/** @var yii\web\View $this */
/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model User model */
/** @var app\models\AppApplicant $appApplicantModel Applicant model */
/** @var app\models\AppApplicantWorkExp $workExpModel Work Experience model for the new entry form */
/** @var array $stepData Custom data for the step (e.g., messages from controller) */
/** @var string $currentStepForView The key of the current step being rendered */
/** @var array $existingWorkExperiences Array of past work experiences, passed from controller */

// $this->title = 'Work Experience';
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
    // console.log('Existing experiences:', " . json_encode($existingWorkExperiences ?? []) . ");

    // Placeholder for future inline edit/delete JS
    // $('#existing-work-experience-table').on('click', '.btn-edit-work-exp', function() { ... });
    // $('#existing-work-experience-table').on('click', '.btn-delete-work-exp', function() { ... });

", \yii\web\View::POS_READY, 'work-exp-step-js');
?>
