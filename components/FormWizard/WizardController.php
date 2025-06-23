<?php

namespace app\components\FormWizard;

use Yii; // Assuming Yii context for session, request, response.
           // For true framework-agnostic, these would need to be abstracted or injected.

/**
 * GenericWizardController
 *
 * Provides a reusable, configuration-driven multi-step form wizard.
 *
 * Key Features:
 * - Step management: Handles navigation between steps (next, previous, specific step).
 * - Data persistence: Stores step data in session (configurable) and manages overall wizard data.
 * - Configurable behavior: Behavior is defined by a configuration array (see config.php).
 * - Callbacks: Allows custom logic injection at various points (e.g., data validation, saving, loading).
 * - View rendering: Renders steps using specified views and a main layout.
 * - AJAX support: Designed to work with AJAX for smoother step transitions (configurable).
 *
 * Basic Usage:
 * 1. Define a configuration array (or use/extend `components/FormWizard/config.php`).
 *    This config specifies steps, views, models, callbacks, etc.
 * 2. In your Yii controller action:
 *    ```php
 *    $wizardConfig = [ ... your custom configuration ... ];
 *    $genericWizard = new GenericWizardController($wizardConfig);
 *    $response = $genericWizard->handleRequest(Yii::$app->request->get('requested_step_key'));
 *    if (Yii::$app->request->isAjax) {
 *        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
 *        return $response;
 *    } else {
 *        // Handle non-AJAX response (e.g., redirect or render full page)
 *        if (isset($response['redirectTo'])) return $this->redirect($response['redirectTo']);
 *        return $this->renderContent($response['fullPageHtml'] ?? 'Error');
 *    }
 *    ```
 * 3. Create view files for each step and a layout file as specified in the configuration.
 * 4. Implement callbacks in the configuration for application-specific logic (validation, saving).
 */
class WizardController
{
    /** @var array The wizard's configuration. Merged from defaults and user-provided config. */
    public $config;
    /** @var string Unique identifier for this wizard instance. */
    protected $wizardId;
    /** @var string Prefix for session keys to store wizard data. */
    protected $sessionKeyPrefix;
    /** @var array Configuration for each step in the wizard. */
    protected $stepsConfig;
    /** @var string The key of the currently active step. */
    protected $currentStepKey;
    /** @var array Holds all data accumulated across steps. Persisted in session if enabled. */
    protected $wizardData = [];

    /**
     * Constructor.
     *
     * @param array $config User-provided configuration for the wizard. This will be merged
     *                      with the default configuration from `config.php`.
     */
    public function __construct(array $config)
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->wizardId = $this->config['wizardId'];
        $this->sessionKeyPrefix = $this->config['sessionKeyPrefix'] . $this->wizardId . '_';
        $this->stepsConfig = $this->config['steps'];

        if ($this->config['enableSessionStorage']) {
            $this->loadWizardDataFromSession();
        }
    }

    protected function getDefaultConfig()
    {
        // Load default config from the config.php file
        // In a Yii context, this might be done differently, e.g., via module params or component config
        $defaultConfigFile = __DIR__ . '/config.php';
        if (file_exists($defaultConfigFile)) {
            return require($defaultConfigFile);
        }
        return []; // Should not happen if file is in place
    }

    protected function getSession()
    {
        // Framework-specific session handling
        // Example for Yii:
        return Yii::$app->session;
    }

    protected function getRequest()
    {
        // Framework-specific request handling
        // Example for Yii:
        return Yii::$app->request;
    }

    protected function loadWizardDataFromSession()
    {
        $session = $this->getSession();
        if ($session && $session->has($this->sessionKeyPrefix . 'data')) {
            $this->wizardData = $session->get($this->sessionKeyPrefix . 'data');
        }
        if ($session && $session->has($this->sessionKeyPrefix . 'current_step')) {
            $this->currentStepKey = $session->get($this->sessionKeyPrefix . 'current_step');
        } else {
            $this->currentStepKey = $this->getFirstStepKey();
        }
    }

    protected function saveWizardDataToSession()
    {
        if ($this->config['enableSessionStorage']) {
            $session = $this->getSession();
            if ($session) {
                $session->set($this->sessionKeyPrefix . 'data', $this->wizardData);
                $session->set($this->sessionKeyPrefix . 'current_step', $this->currentStepKey);
            }
        }
    }

    protected function clearWizardSession()
    {
        if ($this->config['enableSessionStorage']) {
            $session = $this->getSession();
            if ($session) {
                $session->remove($this->sessionKeyPrefix . 'data');
                $session->remove($this->sessionKeyPrefix . 'current_step');
            }
        }
    }

    public function getFirstStepKey()
    {
        return array_key_first($this->stepsConfig);
    }

    public function getLastStepKey()
    {
        return array_key_last($this->stepsConfig);
    }

    public function getStepConfig($stepKey)
    {
        return $this->stepsConfig[$stepKey] ?? null;
    }

    public function getCurrentStepConfig()
    {
        return $this->getStepConfig($this->currentStepKey);
    }

    public function getNextStepKey($currentStepKey)
    {
        $keys = array_keys($this->stepsConfig);
        $currentIndex = array_search($currentStepKey, $keys);
        if ($currentIndex !== false && isset($keys[$currentIndex + 1])) {
            return $keys[$currentIndex + 1];
        }
        return null;
    }

    public function getPreviousStepKey($currentStepKey)
    {
        $keys = array_keys($this->stepsConfig);
        $currentIndex = array_search($currentStepKey, $keys);
        if ($currentIndex !== false && isset($keys[$currentIndex - 1])) {
            return $keys[$currentIndex - 1];
        }
        return null;
    }

    /**
     * Main handler for wizard actions.
     * Determines current step, handles POST data, navigation.
     *
     * @param string|null $requestedStepKey The step key requested via GET or navigation.
     * @return array Response data, typically including HTML for the step, status, messages.
     */
    public function handleRequest($requestedStepKey = null)
    {
        $request = $this->getRequest();
        $isPost = $request->isPost; // Assumes Yii-like request object

        // Determine current step
        if ($requestedStepKey && isset($this->stepsConfig[$requestedStepKey])) {
            // TODO: Add logic to prevent jumping to future steps if not allowed
            $this->currentStepKey = $requestedStepKey;
        } elseif (!$this->currentStepKey) {
            $this->currentStepKey = $this->getFirstStepKey();
        }

        $currentStepConfig = $this->getCurrentStepConfig();
        if (!$currentStepConfig) {
            // This should not happen if logic is correct
            return $this->renderError('Invalid step configuration.');
        }

        // Handle POST request (form submission for current step)
        if ($isPost) {
            $postData = $request->post(); // Assumes Yii-like request object
            $action = $postData['wizard_action'] ?? 'next'; // 'next', 'previous', 'save', 'cancel'

            if ($action === 'cancel') {
                return $this->handleCancel();
            }

            // Process current step data
            $validationErrors = [];
            $isValid = $this->processStepData($this->currentStepKey, $postData, $validationErrors);

            if ($isValid) {
                // Save step data (e.g., to session or wizardData property)
                $this->saveCurrentStepData($postData);

                if ($action === 'save' && $this->currentStepKey === $this->getLastStepKey()) {
                    return $this->handleFinalSave();
                } elseif ($action === 'next') {
                    $nextStepKey = $this->getNextStepKey($this->currentStepKey);
                    if ($nextStepKey) {
                        $this->currentStepKey = $nextStepKey;
                    } else {
                        // Already on the last step, trying to go next (should be 'save' action)
                        // Or, could be a multi-page form that saves at the end.
                        // For now, assume if 'next' on last step, it's an implicit save or error.
                        return $this->handleFinalSave(); // Or specific logic
                    }
                } elseif ($action === 'previous') {
                     $prevStepKey = $this->getPreviousStepKey($this->currentStepKey);
                    if ($prevStepKey) {
                        $this->currentStepKey = $prevStepKey;
                    }
                }
            } else {
                // Validation failed, re-render current step with errors
                return $this->renderStep($this->currentStepKey, $postData, $validationErrors);
            }
        }

        // Save current state to session before rendering
        $this->saveWizardDataToSession();

        // Render the current step (GET request or after successful POST navigation)
        return $this->renderStep($this->currentStepKey, $this->wizardData[$this->currentStepKey] ?? []);
    }

    protected function processStepData($stepKey, array $postData, &$errors = [])
    {
        $stepConfig = $this->getStepConfig($stepKey);
        if (isset($stepConfig['onBeforeProcess']) && is_callable($stepConfig['onBeforeProcess'])) {
            if (call_user_func_array($stepConfig['onBeforeProcess'], [$stepConfig, &$postData, $this->wizardData]) === false) {
                $errors['processing'] = 'Step pre-processing failed.';
                return false;
            }
        }

        $isValid = true;
        if (isset($this->config['callbacks']['validateStepData']) && is_callable($this->config['callbacks']['validateStepData'])) {
            // Extract relevant data for the step from $postData based on form field names.
            // This might need a more sophisticated way to map postData to stepData.
            $stepFormAttributes = $this->getStepFormAttributes($stepKey, $postData);
            $isValid = call_user_func_array($this->config['callbacks']['validateStepData'], [$stepKey, $stepFormAttributes, $stepConfig, &$errors]);
        } elseif (isset($stepConfig['modelClass'])) {
            // Basic model validation if no custom callback
            $model = new $stepConfig['modelClass']();
            if (!empty($stepConfig['scenario'])) {
                $model->scenario = $stepConfig['scenario'];
            }
            // Yii-specific: $model->load($postData) expects data in formName structure.
            // We need to ensure $postData is structured correctly or provide a mapping.
            // For now, assume $postData might contain the model's form name as a key.
            $formName = (new \ReflectionClass($stepConfig['modelClass']))->getShortName();
            if ($model->load($postData) && $model->validate()) { // Assumes $postData[$formName]
                 $this->wizardData[$stepKey] = $model->getAttributes(); // Store validated attributes
            } else {
                $errors = $model->getErrors();
                $isValid = false;
            }
        }

        if (isset($stepConfig['onAfterProcess']) && is_callable($stepConfig['onAfterProcess'])) {
            // $processedData would be the validated and possibly transformed data for the step
            $processedData = $isValid ? ($this->wizardData[$stepKey] ?? $postData) : $postData;
            call_user_func_array($stepConfig['onAfterProcess'], [$stepConfig, $isValid, $processedData, &$this->wizardData]);
        }
        return $isValid;
    }

    protected function getStepFormAttributes($stepKey, array $postData)
    {
        // Placeholder: Logic to extract only relevant form fields for the current step.
        // This is important if multiple models/forms are present in $postData.
        // For Yii, if each step uses a model, $postData[$model->formName()] is typical.
        // For now, returning all postData, assuming step validation handles what it needs.
        $stepConfig = $this->getStepConfig($stepKey);
        if (isset($stepConfig['modelClass'])) {
            $model = new $stepConfig['modelClass']();
            $formName = $model->formName();
            if (isset($postData[$formName])) {
                return $postData[$formName];
            }
        }
        return $postData; // Fallback, might need refinement
    }


    protected function saveCurrentStepData(array $postData)
    {
        $stepConfig = $this->getCurrentStepConfig();
        $stepDataToSave = $this->getStepFormAttributes($this->currentStepKey, $postData);


        // If model validation was used, attributes might already be in $this->wizardData[$this->currentStepKey]
        // This part ensures data submitted (and validated) is stored.
        if (isset($stepConfig['modelClass']) && isset($this->wizardData[$this->currentStepKey])) {
             // Data already set by model validation in processStepData
        } else {
            // Store raw (but validated) data if no model or model validation didn't directly update wizardData
            $this->wizardData[$this->currentStepKey] = $stepDataToSave;
        }


        if (isset($this->config['callbacks']['saveStepData']) && is_callable($this->config['callbacks']['saveStepData'])) {
            call_user_func_array($this->config['callbacks']['saveStepData'], [$this->currentStepKey, $this->wizardData[$this->currentStepKey], &$this->wizardData]);
        }
        $this->saveWizardDataToSession();
    }

    protected function handleFinalSave()
    {
        if (isset($this->config['onWizardComplete']) && is_callable($this->config['onWizardComplete'])) {
            $result = call_user_func($this->config['onWizardComplete'], $this->wizardData);
            if ($result['success'] && $this->config['autoClearSessionOnComplete']) {
                $this->clearWizardSession();
            }
            // Result should include 'success' (bool) and 'message' (string), optionally 'redirectUrl'
            return $this->renderResponse($result);
        }
        // Default success if no callback
        if ($this->config['autoClearSessionOnComplete']) {
            $this->clearWizardSession();
        }
        return $this->renderResponse(['success' => true, 'message' => 'Wizard completed successfully.']);
    }

    protected function handleCancel()
    {
        if (isset($this->config['onWizardCancel']) && is_callable($this->config['onWizardCancel'])) {
            $result = call_user_func($this->config['onWizardCancel'], $this->wizardData);
             if ($result['success'] && $this->config['autoClearSessionOnCancel']) {
                $this->clearWizardSession();
            }
            return $this->renderResponse($result);
        }
        if ($this->config['autoClearSessionOnCancel']) {
            $this->clearWizardSession();
        }
        $redirectUrl = $this->config['buttons']['cancel']['url'] ?? ['index']; // Default cancel URL
        return $this->renderResponse(['success' => true, 'message' => 'Wizard cancelled.', 'redirectUrl' => $redirectUrl]);
    }

    /**
     * Renders a single step.
     *
     * @param string $stepKey The key of the step to render.
     * @param array $formData Data to populate the form with.
     * @param array $errors Validation errors.
     * @return array Response data.
     */
    public function renderStep($stepKey, array $formData = [], array $errors = [])
    {
        $stepConfig = $this->getStepConfig($stepKey);
        if (!$stepConfig) {
            return $this->renderError("Step '{$stepKey}' not found.");
        }

        $view = $stepConfig['view'];
        $model = null;
        if (isset($stepConfig['modelClass'])) {
            $model = new $stepConfig['modelClass']();
            if (!empty($stepConfig['scenario'])) {
                $model->scenario = $stepConfig['scenario'];
            }
            // Populate model with formData.
            // Yii's $model->load() expects data structured with formName.
            // If $formData is already structured (e.g. from $model->getAttributes()), this works.
            // If $formData is flat, need $model->setAttributes($formData, false).
            $formName = $model->formName();
            if(isset($formData[$formName])) { // Data from POST might be structured
                $model->load($formData);
            } elseif (!empty($formData)) { // Data from session/wizardData might be flat array of attributes
                 $model->setAttributes($formData, false);
            }
            $model->addErrors($errors); // Add any external errors
        }

        $viewData = [
            'wizardController' => $this, // Pass controller for access to helper methods
            'currentStepKey' => $stepKey,
            'stepConfig' => $stepConfig,
            'model' => $model, // Pass the model if configured
            'formData' => $formData, // Pass raw form data as well
            'errors' => $errors,
            'allStepsConfig' => $this->stepsConfig,
            'navigationHtml' => $this->renderNavigation(),
            'buttonsHtml' => $this->renderButtons(),
        ];

        if (isset($stepConfig['onAfterLoad']) && is_callable($stepConfig['onAfterLoad'])) {
            call_user_func_array($stepConfig['onAfterLoad'], [$stepConfig, $this->wizardData, &$viewData]);
        }

        // This part is framework-dependent for rendering views.
        // For Yii, Yii::$app->controller->renderPartial($view, $viewData) or similar.
        // For now, let's assume a simple include for demonstration if view is a file path.
        $htmlContent = '';
        if (YII_DEBUG && strpos($view, '@') === 0) { // Yii alias
            $viewPath = Yii::getAlias($view . '.php'); // Assuming .php, adjust if needed
        } else {
            $viewPath = $view; // Assume direct path or handle differently
        }

        if (file_exists($viewPath)) {
            ob_start();
            extract($viewData); // Make variables available to the view file
            require $viewPath;
            $htmlContent = ob_get_clean();
        } else {
             $htmlContent = "<div class='alert alert-danger'>View file not found: {$viewPath} (resolved from {$view})</div>";
        }

        $isAjax = $this->config['ajaxEnabled'] && $this->getRequest()->isAjax;

        if ($isAjax) {
            return [
                'success' => empty($errors),
                'html' => $htmlContent,
                'currentStep' => $stepKey,
                'errors' => $errors,
                'isLastStep' => $this->currentStepKey === $this->getLastStepKey(),
                'isFirstStep' => $this->currentStepKey === $this->getFirstStepKey(),
            ];
        } else {
            // For non-AJAX, we'd typically render a full layout.
            // The $htmlContent is just the step's content.
            // This needs to be integrated into a layout.
            $layout = $this->config['viewLayout'];
            $layoutPath = Yii::getAlias($layout . '.php');
            if (file_exists($layoutPath)) {
                 ob_start();
                 extract(array_merge($viewData, ['content' => $htmlContent]));
                 require $layoutPath;
                 $fullHtml = ob_get_clean();
                 // This would typically be returned as a Response object in Yii.
                 // For now, just returning the HTML string for simplicity.
                 return ['fullPageHtml' => $fullHtml];
            }
            return ['html' => "Layout not found. Step Content: " . $htmlContent]; // Fallback
        }
    }

    public function renderNavigation()
    {
        // Placeholder for navigation rendering logic (tabs, pills, progress bar)
        // This would use $this->config['navigation'] and $this->stepsConfig
        $navType = $this->config['navigation']['type'] ?? 'tabs';
        $items = [];
        $currentStepIndex = array_search($this->currentStepKey, array_keys($this->stepsConfig));

        foreach ($this->stepsConfig as $key => $step) {
            $index = array_search($key, array_keys($this->stepsConfig));
            $isCompleted = isset($this->wizardData[$key]); // Basic check, could be more robust
            $isDisabled = $index > $currentStepIndex && !$isCompleted; // Simplistic disabling logic

            $items[] = [
                'label' => $step['title'] ?? ucfirst(str_replace('_', ' ', $key)),
                'url' => '#', // JS will handle navigation
                'active' => $key === $this->currentStepKey,
                'disabled' => $isDisabled,
                'stepKey' => $key,
                'isCompleted' => $isCompleted,
            ];
        }

        // In Yii, we might render a Nav widget. For now, basic HTML.
        if ($navType === 'tabs' && class_exists('\yii\bootstrap5\Nav')) {
            return \yii\bootstrap5\Nav::widget([
                'items' => array_map(function($item) {
                    return [
                        'label' => $item['label'],
                        'url' => 'javascript:void(0);',
                        'active' => $item['active'],
                        'disabled' => $item['disabled'],
                        'linkOptions' => ['data-step' => $item['stepKey'], 'class' => $item['disabled'] ? 'disabled' : ''],
                    ];
                }, $items),
                'options' => ['class' => 'nav nav-tabs wizard-navigation mb-3'],
            ]);
        }

        $html = "<ul class='nav nav-{$navType} wizard-navigation mb-3'>";
        foreach ($items as $item) {
            $activeClass = $item['active'] ? 'active' : '';
            $disabledClass = $item['disabled'] ? 'disabled' : '';
            $html .= "<li class='nav-item'><a class='nav-link {$activeClass} {$disabledClass}' href='#' data-step='{$item['stepKey']}'>{$item['label']}</a></li>";
        }
        $html .= "</ul>";
        return $html;
    }

    public function renderButtons()
    {
        // Placeholder for button rendering logic
        $buttonsConfig = $this->config['buttons'];
        $html = "<div class='wizard-buttons mt-3'>";

        if ($this->currentStepKey !== $this->getFirstStepKey()) {
            $prevConfig = $buttonsConfig['previous'];
            $html .= "<button type='submit' name='wizard_action' value='previous' class='{$prevConfig['options']['class'] ?? 'btn btn-secondary'} me-2'>{$prevConfig['label']}</button>";
        }

        if ($this->currentStepKey !== $this->getLastStepKey()) {
            $nextConfig = $buttonsConfig['next'];
            $html .= "<button type='submit' name='wizard_action' value='next' class='{$nextConfig['options']['class'] ?? 'btn btn-primary'} me-2'>{$nextConfig['label']}</button>";
        } else {
            $saveConfig = $buttonsConfig['save'];
            $html .= "<button type='submit' name='wizard_action' value='save' class='{$saveConfig['options']['class'] ?? 'btn btn-success'}'>{$saveConfig['label']}</button>";
        }

        if(isset($buttonsConfig['cancel'])) {
            $cancelConfig = $buttonsConfig['cancel'];
            $cancelUrl = '#'; // JS should handle cancel with AJAX or redirect
            if(is_array($cancelConfig['url'])) $cancelUrl = Yii::$app->urlManager->createUrl($cancelConfig['url']); // Yii specific URL generation

             $html .= " <button type='submit' name='wizard_action' value='cancel' class='{$cancelConfig['options']['class'] ?? 'btn btn-warning'} ms-2'>{$cancelConfig['label']}</button>";
            // Or as a link:
            // $html .= " <a href='{$cancelUrl}' class='{$cancelConfig['options']['class'] ?? 'btn btn-warning'} ms-2 wizard-cancel-btn'>{$cancelConfig['label']}</a>";
        }

        $html .= "</div>";
        return $html;
    }


    protected function renderError($message)
    {
        // Framework-agnostic error display for now
        $isAjax = $this->config['ajaxEnabled'] && $this->getRequest()->isAjax;
        if ($isAjax) {
            return ['success' => false, 'message' => $message, 'html' => "<div class='alert alert-danger'>Error: {$message}</div>"];
        } else {
            // In a full page context, this might throw an exception or render an error view
            return ['html' => "<div class='alert alert-danger'>Error: {$message}</div>"];
        }
    }

    protected function renderResponse(array $data)
    {
        // Standardize response format, especially for AJAX
        $isAjax = $this->config['ajaxEnabled'] && $this->getRequest()->isAjax;
        if ($isAjax) {
            // Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $data; // Will be JSON encoded by Yii or the calling controller action
        } else {
            // Handle non-AJAX response, e.g. redirect or display full page
            if (isset($data['redirectUrl'])) {
                // Yii::$app->response->redirect($data['redirectUrl'])->send(); exit;
                // For now, just indicate redirect
                return ['message' => $data['message'] ?? 'Redirecting...', 'redirectTo' => $data['redirectUrl']];
            }
            // Fallback: display message (e.g. on the same page or a generic success/error page)
            return ['html' => $data['message'] ?? 'Operation completed.'];
        }
    }

    // Helper method to get an instance of the wizard for use in views or controllers
    public static function getInstance(array $configOverrides = [], $wizardId = 'defaultWizard')
    {
        $baseConfigPath = __DIR__ . '/config.php';
        $baseConfig = file_exists($baseConfigPath) ? require($baseConfigPath) : [];
        $config = array_replace_recursive($baseConfig, ['wizardId' => $wizardId], $configOverrides); // Ensure wizardId from param is respected
        return new static($config);
    }
}
