<?php

namespace app\controllers;

use Yii;
use app\models\AppApplicantUser;
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;
use app\models\search\AppApplicantUserSearch;
use yii\web\NotFoundHttpException;
use app\models\AppApplicant;
use yii\helpers\ArrayHelper;
use yii\helpers\Html; // Added for Html::errorSummary

class ApplicantUserController extends Controller
{
    const STEP_PERSONAL_DETAILS = 'personal-details';
    const STEP_APPLICANT_SPECIFICS = 'applicant-specifics';
    const STEP_ACCOUNT_SETTINGS = 'account-settings';

    private $_steps = [
        self::STEP_PERSONAL_DETAILS,
        self::STEP_APPLICANT_SPECIFICS,
        self::STEP_ACCOUNT_SETTINGS,
    ];

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class' => AccessControlBehavior::class,
                ],
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                        // Add other actions here if they need verb filtering
                    ],
                ],
            ]
        );
    }

    public function actionUpdateWizard($currentStep = null, $applicant_user_id = null)
    {
        $session = Yii::$app->session;
        $request = Yii::$app->request;
        $wizardDataKeyPrefix = 'applicant_wizard_';

        // Initialize applicant_user_id: from param, then session, then null
        if ($applicant_user_id === null) {
            $applicant_user_id = $session->get($wizardDataKeyPrefix . 'applicant_user_id');
        } else {
            if ($session->has($wizardDataKeyPrefix . 'applicant_user_id') && $session->get($wizardDataKeyPrefix . 'applicant_user_id') != $applicant_user_id) {
                foreach ($this->_steps as $s) {
                    $session->remove($wizardDataKeyPrefix . 'data_step_' . $s);
                }
            }
            $session->set($wizardDataKeyPrefix . 'applicant_user_id', $applicant_user_id);
        }

        // Determine current step for rendering/processing
        // $requestedStepInUrl is the step from the URL, if any. For AJAX GETs to specific steps.
        $requestedStepInUrl = $currentStep;
        if ($currentStep === null) { // If no step in URL
            // For POST, the submitted step is what we process. For GET, it's the last known step or default.
            $currentStep = $session->get($wizardDataKeyPrefix . 'current_step', self::STEP_PERSONAL_DETAILS);
        }
         if (!in_array($currentStep, $this->_steps)) {
            $currentStep = self::STEP_PERSONAL_DETAILS; // Default to first step if invalid
        }


        // Load or instantiate models
        $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        $appApplicantModel = ($applicant_user_id && $model->appApplicant) ? $model->appApplicant : new AppApplicant();
        if ($applicant_user_id && !$appApplicantModel->applicant_user_id) {
            $appApplicantModel->applicant_user_id = $applicant_user_id;
        }

        $stepRenderData = ['message' => null]; // For non-AJAX error messages primarily
        $activeRenderStep = $currentStep; // Step to actually render view for

        if ($request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }

        if ($request->isPost) {
            $postData = $request->post();
            // $currentProcessingStep is the step whose data is being submitted
            $currentProcessingStep = $postData['current_step_validated'] ?? $session->get($wizardDataKeyPrefix . 'current_step', self::STEP_PERSONAL_DETAILS);
            if (!in_array($currentProcessingStep, $this->_steps)) $currentProcessingStep = self::STEP_PERSONAL_DETAILS;

            $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $currentProcessingStep;
            $isValid = false;

            if (isset($postData['wizard_cancel'])) { // Should not happen for AJAX, but good to keep
                foreach ($this->_steps as $s) { $session->remove($wizardDataKeyPrefix . 'data_step_' . $s); }
                $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                $session->remove($wizardDataKeyPrefix . 'current_step');
                if ($request->isAjax) return ['success' => true, 'cancelled' => true, 'redirectUrl' => \yii\helpers\Url::to(['index'])];
                Yii::$app->session->setFlash('info', 'Wizard cancelled.');
                return $this->redirect(['index']);
            }

            $currentStepIndex = array_search($currentProcessingStep, $this->_steps);

            // Determine action: previous, next, save (AJAX might not use 'wizard_previous' button directly)
            $action = '';
            if (isset($postData['wizard_next'])) $action = 'next';
            elseif (isset($postData['wizard_save'])) $action = 'save';
            // For AJAX, the 'action' might be implicitly 'validate_and_proceed' or 'validate_and_save'

            // --- Model Loading and Validation for POSTed step ---
            if ($currentProcessingStep === self::STEP_PERSONAL_DETAILS) {
                $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
                if ($model->load($postData) && $model->validate()) {
                    $session->set($stepSessionKey, $model->getAttributes());
                    $isValid = true;
                    if ($model->isNewRecord) {
                        if ($model->save(false)) { // Save validated attributes
                            $applicant_user_id = $model->applicant_user_id;
                            $session->set($wizardDataKeyPrefix . 'applicant_user_id', $applicant_user_id);
                            $appApplicantModel->applicant_user_id = $applicant_user_id;
                        } else { $isValid = false; $stepRenderData['message'] = 'Failed to save personal details.'; Yii::error($model->errors); }
                    } else { // Existing record
                        if (!$model->save(false)) { $isValid = false; $stepRenderData['message'] = 'Failed to update personal details.'; Yii::error($model->errors); }
                    }
                } else { $isValid = false; if(empty($stepRenderData['message'])) $stepRenderData['message'] = 'Please correct errors in Personal Details.'; }
            } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                // $appApplicantModel->scenario = ...;
                if ($appApplicantModel->load($postData) && $appApplicantModel->validate()) {
                    $session->set($stepSessionKey, $appApplicantModel->getAttributes());
                    $isValid = true;
                } else { $isValid = false; if(empty($stepRenderData['message'])) $stepRenderData['message'] = 'Please correct errors in Applicant Specifics.'; }
            } elseif ($currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) {
                $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
                if ($model->load($postData) && $model->validate()) {
                    $session->set($stepSessionKey, $model->getAttributes(['username', 'password', 'profile_image', 'change_pass']));
                    $isValid = true;
                } else { $isValid = false; if(empty($stepRenderData['message'])) $stepRenderData['message'] = 'Please correct errors in Account Settings.'; }
            }
            // --- End Model Loading and Validation ---

            if ($isValid) {
                if ($action === 'next') {
                    if ($currentStepIndex < count($this->_steps) - 1) {
                        $activeRenderStep = $this->_steps[$currentStepIndex + 1];
                    } else { // Should not happen if 'next' is hidden on last step
                        $activeRenderStep = $currentProcessingStep;
                    }
                    $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
                    if ($request->isAjax) {
                        // Reload models for the new step with data from session if any
                        list($nextModel, $nextAppApplicantModel) = $this->loadModelsForStep($activeRenderStep, $applicant_user_id, $session, $wizardDataKeyPrefix);
                        $html = $this->renderAjax($activeRenderStep, ['model' => $nextModel, 'appApplicantModel' => $nextAppApplicantModel, 'stepData' => [], 'steps' => $this->_steps, 'currentStepForView' => $activeRenderStep]);
                        return ['success' => true, 'html' => $html, 'nextStep' => $activeRenderStep, 'applicant_user_id' => $applicant_user_id];
                    }
                } elseif ($action === 'save' && $currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) {
                    // Final Save Logic (already in original code, needs AJAX adaptation)
                    $finalSaveResult = $this->performFinalSave($applicant_user_id, $session, $wizardDataKeyPrefix);
                    if ($finalSaveResult['success']) {
                        if ($request->isAjax) return ['success' => true, 'completed' => true, 'redirectUrl' => \yii\helpers\Url::to(['view', 'applicant_user_id' => $applicant_user_id])];
                        Yii::$app->session->setFlash('success', 'Applicant details saved successfully.');
                        return $this->redirect(['view', 'applicant_user_id' => $applicant_user_id]);
                    } else {
                        $activeRenderStep = $currentProcessingStep; // Stay on current step
                        $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
                        if ($request->isAjax) return ['success' => false, 'errors' => $finalSaveResult['errors'], 'message' => $finalSaveResult['message']];
                        $stepRenderData['message'] = $finalSaveResult['message'];
                    }
                } else { // Valid, but no specific action like next/save (e.g. tab click to a valid step, handled by GET in AJAX usually)
                     $activeRenderStep = $currentProcessingStep; // Stay on current step
                     $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
                }
            } else { // Not $isValid (validation failed)
                $activeRenderStep = $currentProcessingStep; // Stay on current step
                $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
                if ($request->isAjax) {
                    $errors = [];
                    if ($currentProcessingStep === self::STEP_PERSONAL_DETAILS || $currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) {
                        $errors = $model->getErrors();
                    } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                        $errors = $appApplicantModel->getErrors();
                    }
                    return ['success' => false, 'errors' => $errors, 'message' => $stepRenderData['message'] ?? 'Validation failed.'];
                }
            }
            // For non-AJAX POST, redirect to GET to show the new $activeRenderStep
            if (!$request->isAjax) {
                return $this->redirect(['update-wizard', 'currentStep' => $activeRenderStep, 'applicant_user_id' => $applicant_user_id]);
            }
            // currentStep variable is updated for rendering below for non-AJAX
            $currentStep = $activeRenderStep;

        } elseif ($request->isAjax && $request->isGet) { // AJAX GET request (e.g., clicking a tab)
            // $currentStep is already set from URL or session
            // $requestedStepInUrl is the step explicitly requested by the client if navigating via tab click
            $targetStep = $requestedStepInUrl ?? $currentStep;
            if (!in_array($targetStep, $this->_steps)) $targetStep = self::STEP_PERSONAL_DETAILS;

            // Security: Check if the target step is accessible (e.g., previous steps completed)
            // For now, assume if applicant_user_id exists, all steps are potentially accessible by tab click if logic allows
            // The front-end JS should disable future tabs based on completion. Here, we primarily serve the content.
            // If !$applicant_user_id, only allow STEP_PERSONAL_DETAILS
            if (!$applicant_user_id && $targetStep !== self::STEP_PERSONAL_DETAILS) {
                 return ['success' => false, 'message' => 'Please complete the first step.', 'redirectToStep' => self::STEP_PERSONAL_DETAILS];
            }

            $session->set($wizardDataKeyPrefix . 'current_step', $targetStep);
            list($renderModel, $renderAppApplicantModel) = $this->loadModelsForStep($targetStep, $applicant_user_id, $session, $wizardDataKeyPrefix);

            $html = $this->renderAjax($targetStep, [
                'model' => $renderModel,
                'appApplicantModel' => $renderAppApplicantModel,
                'stepData' => [], // No specific error messages for GET usually
                'steps' => $this->_steps,
                'currentStepForView' => $targetStep // Pass the specific step being rendered
            ]);
            return ['success' => true, 'html' => $html, 'currentStep' => $targetStep, 'applicant_user_id' => $applicant_user_id];
        } else { // Non-AJAX GET request (initial page load)
            $session->set($wizardDataKeyPrefix . 'current_step', $currentStep);
        }

        // --- Common logic for rendering (mostly for non-AJAX initial load or post-redirect-get) ---
        // Load data from session for the step that will be rendered ($currentStep determined above)
        list($model, $appApplicantModel) = $this->loadModelsForStep($currentStep, $applicant_user_id, $session, $wizardDataKeyPrefix, $model, $appApplicantModel);

        // Set scenario for the actual step being rendered (for non-AJAX)
        if ($currentStep === self::STEP_PERSONAL_DETAILS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
        } elseif ($currentStep === self::STEP_ACCOUNT_SETTINGS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
        } // Add for $appApplicantModel if needed for its own scenarios

        return $this->render('update-wizard', [
            'currentStep' => $currentStep,
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
            'stepData' => $stepRenderData, // Contains messages from non-AJAX POST errors
            'steps' => $this->_steps,
        ]);
    }

    // Helper function to load models for a specific step
    protected function loadModelsForStep($step, $applicant_user_id, $session, $wizardDataKeyPrefix, $existingModel = null, $existingAppApplicantModel = null)
    {
        $model = $existingModel ?? ($applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser());
        $appApplicantModel = $existingAppApplicantModel ?? (($applicant_user_id && $model->appApplicant) ? $model->appApplicant : new AppApplicant());

        if ($applicant_user_id && !$appApplicantModel->applicant_user_id) {
            $appApplicantModel->applicant_user_id = $applicant_user_id;
        }

        $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $step;
        $stepDataFromSession = $session->get($stepSessionKey, []);

        if (!empty($stepDataFromSession)) {
            if ($step === self::STEP_PERSONAL_DETAILS) {
                $model->setAttributes($stepDataFromSession, false);
            } elseif ($step === self::STEP_APPLICANT_SPECIFICS) {
                $appApplicantModel->setAttributes($stepDataFromSession, false);
            } elseif ($step === self::STEP_ACCOUNT_SETTINGS) {
                $model->setAttributes($stepDataFromSession, false); // Assuming account settings are on AppApplicantUser
            }
        }
        return [$model, $appApplicantModel];
    }

    // Helper function for final save operation
    protected function performFinalSave($applicant_user_id, $session, $wizardDataKeyPrefix)
    {
        if (empty($applicant_user_id)) {
            return ['success' => false, 'message' => 'Applicant User ID is missing. Cannot save.', 'errors' => []];
        }

        $finalModel = $this->findModel($applicant_user_id);
        $finalAppApplicantModel = $finalModel->getAppApplicant()->one() ?? new AppApplicant();
        $finalAppApplicantModel->applicant_user_id = $applicant_user_id;

        $personalDetailsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
        $applicantSpecificsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_APPLICANT_SPECIFICS, []);
        $accountSettingsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_ACCOUNT_SETTINGS, []);

        // Set attributes from all steps. Personal details already saved/updated if changed.
        // $finalModel->setAttributes($personalDetailsData, false); // Not needed if already saved
        $finalModel->setAttributes($accountSettingsData, false); // Apply account settings
        $finalAppApplicantModel->setAttributes($applicantSpecificsData, false);

        $finalModel->scenario = AppApplicantUser::SCENARIO_DEFAULT; // Set default scenario for final save

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Re-validate before final save, especially if scenarios change or data comes from multiple sources
            // For this example, assuming data from session is "trusted" as it was validated per step.
            // However, a full model validation might be safer.
            // $finalModel->validate() and $finalAppApplicantModel->validate()

            if ($finalModel->save()) { // This will save changes from account settings step
                if ($finalAppApplicantModel->save()) {
                    $transaction->commit();
                    foreach ($this->_steps as $s) { $session->remove($wizardDataKeyPrefix . 'data_step_' . $s); }
                    $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                    $session->remove($wizardDataKeyPrefix . 'current_step');
                    return ['success' => true];
                } else {
                    $transaction->rollBack();
                    Yii::error($finalAppApplicantModel->errors);
                    return ['success' => false, 'message' => 'Error saving applicant specifics: ' . Html::errorSummary($finalAppApplicantModel), 'errors' => $finalAppApplicantModel->getErrors()];
                }
            } else {
                $transaction->rollBack();
                Yii::error($finalModel->errors);
                return ['success' => false, 'message' => 'Error saving applicant user details: ' . Html::errorSummary($finalModel), 'errors' => $finalModel->getErrors()];
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            return ['success' => false, 'message' => 'An error occurred: ' . $e->getMessage(), 'errors' => []];
        }
    }

    /**
     * Lists all AppApplicantUser models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AppApplicantUserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing AppApplicantUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $applicant_user_id Applicant User ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($applicant_user_id)
    {
        $model = $this->findModel($applicant_user_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'applicant_user_id' => $model->applicant_user_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    public function actionUserList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $data = AppApplicantUser::find()
                ->select(['id' => 'applicant_user_id', 'text' => "CONCAT(first_name, ' ', last_name, ' (', email_address, ')')"])
                ->where(['like', 'first_name', $q])
                ->orWhere(['like', 'last_name', $q])
                ->orWhere(['like', 'email_address', $q])
                ->limit(20)
                ->asArray()
                ->all();
            $out['results'] = $data;
        }
        return $out;
    }

    /**
     * Finds the AppApplicantUser model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $applicant_user_id Applicant User ID
     * @return AppApplicantUser the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($applicant_user_id)
    {
        if (($model = AppApplicantUser::findOne(['applicant_user_id' => $applicant_user_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
