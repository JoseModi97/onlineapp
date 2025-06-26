# How to Add a New Step to the Applicant Update Wizard

This document outlines the steps required to add a new step to the applicant update wizard in this application. The wizard is primarily managed by the `ApplicantUserController` and its associated views and JavaScript files.

## Steps to Add a New Wizard Step

1.  **Define Step Key in Controller**:
    *   Open `controllers/ApplicantUserController.php`.
    *   Define a new constant for your step's unique key. For example:
        ```php
        const STEP_NEW_STEP_NAME = 'new-step-name';
        ```
    *   Add this new constant to the `$_steps` private array:
        ```php
        private $_steps = [
            self::STEP_PERSONAL_DETAILS,
            self::STEP_APPLICANT_SPECIFICS,
            self::STEP_EDUCATION,
            self::STEP_NEW_STEP_NAME, // Add new step here
            self::STEP_ACCOUNT_SETTINGS,
        ];
        ```
        Ensure the order in the array reflects the desired order in the wizard.

2.  **Handle Step Logic in `actionUpdateWizard`**:
    *   Still in `controllers/ApplicantUserController.php`, locate the `actionUpdateWizard` method.
    *   You'll need to add logic to handle your new step when a POST request is made. This typically involves:
        *   Loading the relevant model(s) for your step.
        *   Loading posted data into the model(s).
        *   Validating the model(s).
        *   If valid, storing the model attributes in the session.
        *   If invalid, preparing error messages.
    *   Example structure for your new step's POST handling block:
        ```php
        // Inside the if ($request->isPost) block, add a new elseif condition
        // $currentProcessingStep is derived from $postData['current_step_validated'] or session.
        elseif ($currentProcessingStep === self::STEP_NEW_STEP_NAME) {
            // Initialize your model(s) for this step.
            // For example, the 'education' step initializes an $educationModel.
            // $newStepModel = new NewStepModel();
            // Ensure it's available in the scope if needed for error reporting in AJAX response.
            //
            // if ($newStepModel->load($postData) && $newStepModel->validate()) {
            //     $session->set($stepSessionKey, $newStepModel->getAttributes());
            //     $isValid = true;
            // } else {
            //     $isValid = false;
            //     $stepRenderData['message'] = 'Please correct errors in New Step Name.';
            //     // If AJAX, errors from $newStepModel->getErrors() are usually returned.
            // }
        }
        ```
    *   Ensure validation errors from your model (e.g., `$newStepModel->getErrors()`) are correctly prepared for the AJAX response if `$isValid` is false. The controller already has a structure for this.

3.  **Update `loadModelsForStep` Method**:
    *   In `controllers/ApplicantUserController.php`, find the `loadModelsForStep` method.
    *   This method is responsible for initializing and populating models when a step is loaded. It currently returns an array like `[$model, $appApplicantModel, $educationModel]`.
    *   Add a condition for your new step. If it uses a new, separate model, you'll need to initialize it and add it to the returned array. The calling code (e.g., in `actionUpdateWizard` and `renderAjax` calls) will need to be updated to expect this new model in the array destructuring.
    *   Example:
        ```php
        // Inside loadModelsForStep method, before the return statement
        // ...
        $newStepModel = null; // Initialize
        if ($step === self::STEP_NEW_STEP_NAME) {
            // $newStepModel = new NewStepModel(); // Or find existing
            // if (!empty($stepDataFromSession)) { // $stepDataFromSession is already loaded for $step
            //    $newStepModel->setAttributes($stepDataFromSession, false);
            // }
        }
        // ...
        // Adjust the return statement:
        // return [$model, $appApplicantModel, $educationModel, $newStepModel];
        ```
    *   Then, in `actionUpdateWizard`, when calling `loadModelsForStep`:
        ```php
        // list($model, $appApplicantModel, $educationModel) = $this->loadModelsForStep(...);
        // would become:
        // list($model, $appApplicantModel, $educationModel, $newStepModel) = $this->loadModelsForStep(...);
        ```
    *   And pass `$newStepModel` to `renderAjax` and the main `render` call for the `update-wizard` view. The partial view for your step will then receive this model.
    *   If your new step uses one of the existing models (e.g., `$model` or `$appApplicantModel`) perhaps with a specific scenario, you might not need to change the return signature of `loadModelsForStep` but would handle its attributes within the existing model's logic for that step.

4.  **Update `performFinalSave` Method**:
    *   In `controllers/ApplicantUserController.php`, find the `performFinalSave` method.
    *   This method is called when the user clicks "Save" on the last step. It gathers all data from the session and saves it to the database.
    *   Add logic to retrieve your new step's data from the session and save it.
    *   Example:
        ```php
        // Inside performFinalSave method
        // $newStepData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_NEW_STEP_NAME, []);
        // if (!empty($newStepData)) {
        //     // $newStepModel = new NewStepModel(); // Or find existing
        //     // $newStepModel->setAttributes($newStepData, false);
        //     // Ensure any necessary foreign keys (like applicant_id) are set.
        //     // if (!$newStepModel->save()) {
        //     //    $transaction->rollBack();
        //     //    return ['success' => false, 'message' => 'Error saving new step data.', 'errors' => $newStepModel->getErrors()];
        //     // }
        // }
        ```

5.  **Create the View File**:
    *   Create a new PHP file in the `views/applicant-user/` directory. The name should correspond to your step key (e.g., `new-step-name.php`).
    *   This file will contain the HTML form elements for your step. It will receive models passed from the controller (e.g., `$model`, `$appApplicantModel`, or your new custom model).
    *   Example structure for `views/applicant-user/new-step-name.php`:
        ```php
        <?php
        use yii\helpers\Html;
        use yii\widgets\ActiveForm; // Or yii\bootstrap5\ActiveForm

        /** @var yii\web\View $this */
        /** @var app\models\YourModelForThisStep $model // Adjust model type as needed */
        /** @var yii\widgets\ActiveForm $form */
        ?>

        <div class="new-step-name-form">
            <?php $form = ActiveForm::begin([
                'id' => 'new-step-form', // Ensure unique ID if needed by JS
                // 'enableAjaxValidation' => true, // If using AJAX validation
            ]); ?>

            <?= $form->field($model, 'attribute_name')->textInput() ?>

            <?php // Add other form fields for your new step ?>

            <?php ActiveForm::end(); ?>
        </div>
        ```
    *   Remember that the form submission is handled via AJAX by `web/js/applicant-wizard-ajax.js`. The form itself doesn't need a submit button; the main wizard buttons ("Next", "Previous", "Save") trigger the logic.

6.  **Update Wizard Navigation UI**:
    *   Open `views/applicant-user/update-wizard.php`.
    *   Locate the `$stepTitles` array.
    *   Add a user-friendly title for your new step, using the step key defined earlier.
        ```php
        $stepTitles = [
            'personal-details' => 'Personal Details',
            'applicant-specifics' => 'Applicant Specifics',
            'education' => 'Education Details',
            'new-step-name' => 'Title for New Step', // Add your new step's title
            'account-settings' => 'Account Settings',
        ];
        ```

7.  **JavaScript Considerations (If Applicable)**:
    *   The existing `web/js/applicant-wizard-ajax.js` is designed to be somewhat generic. For most new steps involving standard form fields, you might not need to change it.
    *   However, if your new step has complex client-side interactions, requires specific JavaScript initialization for its content (e.g., date pickers, custom file upload handling beyond what `STEP_ACCOUNT_SETTINGS` does), or if its form structure is very different, you might need to:
        *   Ensure any new JavaScript for your step is re-initialized when the step content is loaded via AJAX (see `handleAjaxResponse` function, the part about re-initializing JS).
        *   If error display for specific fields is custom, check the error handling logic in `handleAjaxResponse`.

8.  **Testing**:
    *   Thoroughly test the new step:
        *   Navigation to and from the step.
        *   Data submission (valid and invalid data).
        *   Validation messages.
        *   Data saving correctly upon final wizard save.
        *   Data correctly loaded when returning to the step.
        *   Interaction with other steps (e.g., dependencies on data from previous steps).

By following these steps, you can integrate a new section into the applicant update wizard. Remember to adapt model names, attribute names, and specific logic to fit the requirements of your new step.
