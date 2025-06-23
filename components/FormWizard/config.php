<?php

/**
 * Default Configuration for the GenericWizardController.
 *
 * This file defines the default structure and options for the wizard.
 * When instantiating GenericWizardController, you can pass an array that overrides
 * these defaults or adds to them.
 *
 * Refer to GenericWizardController documentation for more details on how these
 * options are used.
 */
return [
    // Unique identifier for this wizard instance. Used for session key prefixing and DOM element IDs.
    'wizardId' => 'defaultWizard',

    // Prefix for session keys. The wizardId is appended to this.
    'sessionKeyPrefix' => 'generic_wizard_',

    // Whether to store step data and current step in the session.
    'enableSessionStorage' => true,

    // Whether AJAX is used for step navigation and form submissions.
    // If true, the controller expects and returns JSON responses for step transitions.
    // If false, full page reloads will occur.
    'ajaxEnabled' => true,

    // Automatically clear wizard session data upon successful completion (onWizardComplete returns success).
    'autoClearSessionOnComplete' => true,

    // Automatically clear wizard session data upon cancellation (onWizardCancel returns success or default cancel).
    'autoClearSessionOnCancel' => true,

    // Default main layout file for rendering the entire wizard page (used in non-AJAX responses).
    // This layout receives $content (the output of the wizard's internal step rendering).
    // It should handle the overall page structure (<html>, <head>, <body>, asset registration).
    'viewLayout' => '@app/components/FormWizard/views/layouts/main.php', // Example, adjust to your app's structure

    // Definition of wizard steps. This is the core of the wizard configuration.
    // Each key is a unique step identifier, and the value is an array of step properties.
    'steps' => [
        /*
        'step_key_1' => [
            'title' => 'Step 1 Title', // Title displayed for the step (e.g., in navigation)
            'view' => '@app/views/path/to/step1/view', // Path or Yii alias to the view file for this step
            'modelClass' => 'app\models\Step1Model', // Optional: Yii model class associated with this step
            'scenario' => 'step1_scenario', // Optional: Scenario for the modelClass

            // Callbacks specific to this step's lifecycle:
            'onBeforeLoad' => function($stepConfig, $allWizardData) {
                // Called before the step view is rendered.
                // Useful for pre-loading data or modifying stepConfig dynamically.
                // `$allWizardData` contains data from all previously completed steps.
                // Return false to prevent step loading (e.g., access denied).
                return true;
            },
            'onAfterLoad' => function($stepConfig, $allWizardData, &$viewData) {
                // Called after the step view data is prepared, but before rendering.
                // Allows modification of `$viewData` passed to the view.
            },
            'onBeforeProcess' => function($stepConfig, &$postData, $allWizardData) {
                // Called when this step is submitted (POST request), before validation.
                // Useful for modifying `$postData` before it's validated or processed.
                // `$allWizardData` contains data from other steps.
                // Return false to stop processing and validation (e.g., if a critical check fails).
                return true;
            },
            'onAfterProcess' => function($stepConfig, $isValid, $processedData, &$allWizardData) {
                // Called after validation and processing of this step's data.
                // `$isValid` indicates if validation passed.
                // `$processedData` is the data for this step after processing (and possibly validation).
                // `$allWizardData` can be modified here if needed (e.g., setting a flag based on this step).
            },
        ],
        'step_key_2' => [ ... ],
        */
    ],

    // Global callbacks for the wizard's lifecycle.
    'callbacks' => [
        /**
         * Called to load initial/existing data for a step when it's about to be displayed.
         * This is particularly useful for populating forms when editing existing records.
         * @param string $stepKey The key of the step being loaded.
         * @param array $allWizardData Data accumulated from other steps so far.
         * @return array Key-value pairs of data to be populated into the step's form/model.
         *               This data will be passed to the step's view.
         */
        'loadStepData' => function($stepKey, $allWizardData) {
            return $allWizardData[$stepKey] ?? []; // Default: return data already in session for this step
        },

        /**
         * Called to validate the data submitted for a specific step.
         * @param string $stepKey The key of the step being validated.
         * @param array $stepData The data submitted for this step (typically from $_POST, filtered).
         * @param array $stepConfig The configuration array for this specific step.
         * @param array &$errors Reference to an array where validation errors should be stored (field => message(s)).
         * @return bool True if validation passes, false otherwise.
         */
        'validateStepData' => function($stepKey, $stepData, $stepConfig, &$errors) {
            // If 'modelClass' is defined in stepConfig, GenericWizardController provides basic model validation.
            // This callback can override it or implement custom validation for steps without models.
            // Example for a model-based step (simplified, real implementation in WizardController is more robust):
            /*
            if (!empty($stepConfig['modelClass'])) {
                $model = new $stepConfig['modelClass']();
                if (!empty($stepConfig['scenario'])) $model->scenario = $stepConfig['scenario'];
                // $model->load($stepData) expects $stepData to be structured like [$model->formName() => attributes]
                // The WizardController's getStepFormAttributes tries to provide this.
                // If $stepData is just attributes, use $model->load($stepData, '').
                if ($model->load($stepData, '') && $model->validate()) return true;
                $errors = $model->getErrors();
                return false;
            }
            */
            return true; // Default: assume valid if no specific validation logic here
        },

        /**
         * Called after a step's data has been successfully validated and is about to be stored
         * (typically in session or the wizard's internal data array).
         * @param string $stepKey The key of the step whose data is being saved.
         * @param array &$currentStepValidatedData Reference to the validated data for the current step.
         *                                         It can be modified here before being stored.
         * @param array &$allWizardData Reference to the array holding all wizard data.
         *                              Allows modification of global wizard state if needed.
         * @return bool True if saving (to internal wizard data) was successful. False can halt wizard.
         */
        'saveStepData' => function($stepKey, &$currentStepValidatedData, &$allWizardData) {
            // The GenericWizardController will by default store $currentStepValidatedData
            // into $allWizardData[$stepKey]. This callback can be used for additional logic.
            return true;
        },
    ],

    /**
     * Called when the wizard is started for the first time or if session data is lost.
     * @param array &$initialData Reference to an array that can be pre-populated with initial wizard data.
     * @return bool Return false to prevent the wizard from starting.
     */
    'onBeforeWizardStart' => function(&$initialData) {
        return true;
    },

    /**
     * Called when the wizard's final "save" action is triggered on the last step and all previous steps are valid.
     * This is where final data persistence (e.g., to database) should occur.
     * @param array $allWizardData An array containing all data collected from all steps.
     * @return array Must return an array with at least 'success' (bool) and 'message' (string).
     *               Optionally, 'redirectUrl' can be provided for non-AJAX redirection.
     *               Example: ['success' => true, 'message' => 'Data saved!', 'redirectUrl' => '/thank-you']
     */
    'onWizardComplete' => function($allWizardData) {
        // Implement final saving logic here.
        // Yii::info("Wizard completed with data: " . print_r($allWizardData, true), __METHOD__);
        return ['success' => true, 'message' => 'Wizard completed successfully!'];
    },

    /**
     * Called when the wizard's "cancel" action is triggered.
     * @param array $allWizardData Current data collected by the wizard.
     * @return array Must return an array with 'success' (bool) and 'message' (string).
     *               Optionally, 'redirectUrl' for non-AJAX. If not provided or null,
     *               default cancel behavior (clearing session, redirecting based on 'buttons.cancel.url') applies.
     */
    'onWizardCancel' => function($allWizardData) {
        return ['success' => true, 'message' => 'Wizard cancelled.']; // Default behavior is fine
    },

    // Configuration for how navigation elements (e.g., tabs, progress bar) are displayed.
    'navigation' => [
        'type' => 'tabs', // Supported types: 'tabs', 'pills'. 'progress_bar' or 'custom' could be added.
        'showStepTitles' => true, // Whether to display step titles in the navigation.
        // 'customNavView' => '@app/path/to/custom/nav/view.php', // For 'custom' type, specify a view file.
    ],

    // Configuration for wizard action buttons (Previous, Next, Save, Cancel).
    'buttons' => [
        'previous' => ['label' => 'Previous', 'options' => ['class' => 'btn btn-secondary me-2']],
        'next'     => ['label' => 'Next',     'options' => ['class' => 'btn btn-primary me-2']],
        'save'     => ['label' => 'Save',     'options' => ['class' => 'btn btn-success']], // Typically shown on the last step
        'cancel'   => [
            'label'   => 'Cancel',
            'options' => ['class' => 'btn btn-warning ms-2'],
            'url'     => ['index'] // Default Yii URL array for redirection if wizard is cancelled.
                                   // JS might handle cancel differently for AJAX.
        ],
    ],
];
