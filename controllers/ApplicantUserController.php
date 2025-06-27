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
use app\models\AppApplicantWorkExp; // Added for new step
use yii\helpers\ArrayHelper;
use yii\helpers\Html; // Added for Html::errorSummary
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class ApplicantUserController extends Controller
{
    const STEP_PERSONAL_DETAILS = 'personal-details';
    const STEP_WORK_EXPERIENCE = 'applicant-work-exp'; // New step
    const STEP_APPLICANT_SPECIFICS = 'applicant-specifics';
    const STEP_ACCOUNT_SETTINGS = 'account-settings';

    private $_steps = [
        self::STEP_PERSONAL_DETAILS,
        self::STEP_WORK_EXPERIENCE, // New step added in order
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
        $requestedStepInUrl = $currentStep;
        if ($currentStep === null) {
            $currentStep = $session->get($wizardDataKeyPrefix . 'current_step', self::STEP_PERSONAL_DETAILS);
        }
        if (!in_array($currentStep, $this->_steps)) {
            $currentStep = self::STEP_PERSONAL_DETAILS;
        }

        $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        // $appApplicantModel and $workExpModel will be loaded by loadModelsForStep shortly
        // list($model, $appApplicantModel, $workExpModel) = $this->loadModelsForStep($currentStep, $applicant_user_id, $session, $wizardDataKeyPrefix, $model, null);
        // No, this initial load is too early for $workExpModel if it's not the current step.
        // $appApplicantModel will be initialized along with $model by loadModelsForStep.
        // Let's ensure $model is primary, $appApplicantModel and $workExpModel are loaded as needed by loadModelsForStep.

        $stepRenderData = ['message' => null];
        $activeRenderStep = $currentStep;

        // Correctly initialize models for the current context (POST or GET)
        // For POST, models are loaded specifically for the processed step.
        // For GET (initial render or AJAX step load), loadModelsForStep handles initialization.
        $appApplicantModel = null; // Will be populated by loadModelsForStep or within POST logic
        $workExpModel = null;      // Will be populated by loadModelsForStep or within POST logic


        if ($request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }

        if ($request->isPost) {
            $postData = $request->post();
            $currentProcessingStep = $postData['current_step_validated'] ?? $session->get($wizardDataKeyPrefix . 'current_step', self::STEP_PERSONAL_DETAILS);
            if (!in_array($currentProcessingStep, $this->_steps)) $currentProcessingStep = self::STEP_PERSONAL_DETAILS;

            // Load models relevant to the current processing step
            // $model is AppApplicantUser, $appApplicantModel is AppApplicant, $workExpModel for work experience
            // We need to ensure $model (AppApplicantUser) is loaded correctly if $applicant_user_id exists
            if ($applicant_user_id && !$model->isNewRecord && $model->applicant_user_id != $applicant_user_id) {
                // If $model was new but $applicant_user_id came from session, or ID mismatch, reload.
                $model = $this->findModel($applicant_user_id);
            }
            // $appApplicantModel and $workExpModel are typically new instances for POST data loading,
            // unless we intend to load existing then modify. Session storage model suggests new instances.
            // $appApplicantModel = new AppApplicant(); // Or load existing if updating directly
            // $workExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);

            $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $currentProcessingStep;
            $isValid = false;

            if (isset($postData['wizard_cancel'])) {
                foreach ($this->_steps as $s) {
                    $session->remove($wizardDataKeyPrefix . 'data_step_' . $s);
                }
                $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                $session->remove($wizardDataKeyPrefix . 'current_step');
                if ($request->isAjax) return ['success' => true, 'cancelled' => true, 'redirectUrl' => \yii\helpers\Url::to(['index'])];
                Yii::$app->session->setFlash('info', 'Wizard cancelled.');
                return $this->redirect(['index']);
            }

            $currentStepIndex = array_search($currentProcessingStep, $this->_steps);
            $action = '';
            // $isSkippingStep = false; // Flag for skip action - REMOVED

            if (isset($postData['wizard_next'])) $action = 'next';
            elseif (isset($postData['wizard_save'])) $action = 'save';
            // elseif (isset($postData['wizard_skip_step'])) { // REMOVED
            //    $action = 'next'; // Skip behaves like 'next' in terms of progression
            //    $isSkippingStep = true;
            // }

            if ($currentProcessingStep === self::STEP_PERSONAL_DETAILS) {
                $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
                // Handle profile image upload specifically for personal details
                $oldProfileImage = $model->profile_image; // Store old image name before load
                $model->profile_image_file = UploadedFile::getInstance($model, 'profile_image_file');

                if ($model->load($postData)) {
                    if (!$model->profile_image_file) {
                        $model->profile_image = $oldProfileImage; // Restore if no new file uploaded
                    }

                    if ($model->validate()) {
                        $isValid = true;
                        // Image processing logic moved from account-settings
                        if ($model->profile_image_file) {
                            $uploadPath = Yii::getAlias('@webroot/img/profile/');
                            if (!is_dir($uploadPath)) {
                                FileHelper::createDirectory($uploadPath);
                            }
                            $uniqueFilename = Yii::$app->security->generateRandomString() . '.' . $model->profile_image_file->extension;
                            $filePath = $uploadPath . $uniqueFilename;
                            if ($model->profile_image_file->saveAs($filePath)) {
                                if ($oldProfileImage && $oldProfileImage !== $uniqueFilename && file_exists($uploadPath . $oldProfileImage)) {
                                    @unlink($uploadPath . $oldProfileImage);
                                }
                                $model->profile_image = $uniqueFilename; // Update model attribute
                            } else {
                                $isValid = false;
                                $model->addError('profile_image_file', 'Could not save the uploaded image.');
                                $stepRenderData['message'] = 'Error saving profile image.';
                            }
                        }

                        if ($isValid) { // Proceed if image processing was successful (or not needed)
                            // Save all relevant attributes for personal details step to session
                            // This will now include username and profile_image (filename)
                            $session->set($stepSessionKey, $model->getAttributes());
                            if ($model->isNewRecord) {
                        if ($model->save(false)) {
                            $applicant_user_id = $model->applicant_user_id;
                            $session->set($wizardDataKeyPrefix . 'applicant_user_id', $applicant_user_id);
                            $appApplicantModel->applicant_user_id = $applicant_user_id;
                        } else {
                            $isValid = false;
                            $stepRenderData['message'] = 'Failed to save personal details.';
                            Yii::error($model->errors);
                        }
                    } else {
                        if (!$model->save(false)) {
                            $isValid = false;
                            $stepRenderData['message'] = 'Failed to update personal details.';
                            Yii::error($model->errors);
                        }
                    }
                } else { // Model load or validation failed
                    $isValid = false;
                    if ($model->hasErrors('profile_image_file') && empty($stepRenderData['message'])) {
                        $stepRenderData['message'] = 'Profile image error: ' . $model->getFirstError('profile_image_file');
                    } elseif (empty($stepRenderData['message'])) {
                        $stepRenderData['message'] = 'Please correct errors in Personal Details. ' . Html::errorSummary($model);
                    }
                }
            } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                // Initialize $appApplicantModel for the current step
                if ($applicant_user_id && $model && !$model->isNewRecord) {
                    // $model is AppApplicantUser, already loaded or re-fetched if $applicant_user_id was present
                    $appApplicantModel = $model->getAppApplicant()->one();
                    if (!$appApplicantModel) {
                        $appApplicantModel = new AppApplicant();
                        $appApplicantModel->applicant_user_id = $model->applicant_user_id; // Link to existing AppApplicantUser
                    }
                } else {
                    // This case means $applicant_user_id is not yet known (e.g. first step not completed, which shouldn't allow reaching this step)
                    // or $model is new (its ID isn't set yet).
                    $appApplicantModel = new AppApplicant();
                    if ($applicant_user_id) { // If an overall $applicant_user_id is known (e.g. from session)
                        $appApplicantModel->applicant_user_id = $applicant_user_id;
                    }
                }

                // Ensure FK is set if $model exists and has an ID, and $appApplicantModel is an object but not linked
                if ($appApplicantModel && $model && !$model->isNewRecord && !$appApplicantModel->applicant_user_id) {
                    $appApplicantModel->applicant_user_id = $model->applicant_user_id;
                }

                if ($appApplicantModel->load($postData) && $appApplicantModel->validate()) {
                    $session->set($stepSessionKey, $appApplicantModel->getAttributes());
                    $isValid = true;
                } else {
                    $isValid = false;
                    if (!$appApplicantModel) { // Should not happen with the above initialization
                        Yii::error("AppApplicantModel was unexpectedly null before validation for STEP_APPLICANT_SPECIFICS.");
                        $stepRenderData['message'] = 'Critical error: Applicant details model not available. Please contact support.';
                    } else {
                        // Set a general message, and Html::errorSummary will be more specific if $appApplicantModel is available
                        $summary = Html::errorSummary($appApplicantModel);
                        $stepRenderData['message'] = 'Please correct errors in Applicant Specifics. ' . $summary;
                    }
                }
            } elseif ($currentProcessingStep === self::STEP_WORK_EXPERIENCE) {
                // Initialize $workExpModel for this step.
                // It's okay if $applicant_user_id is not yet final, or $appApplicantModel->applicant_id is not yet set.
                // We are saving to session. Linkage will happen in performFinalSave.
                // if ($isSkippingStep) { // REMOVED SKIP LOGIC
                // // If skipping, mark as valid and clear any previous session data for this step
                // $session->remove($stepSessionKey); // Remove data for this step from session
                // $isValid = true;
                // } else { // REMOVED ELSE FOR SKIP LOGIC
                $workExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);
                if ($workExpModel->load($postData)) {
                    if ($workExpModel->validate()) {
                        // Data is valid, store in session first
                        $session->set($stepSessionKey, $workExpModel->getAttributes());
                        $isValid = true;

                        // Now, attempt to save immediately to DB since it's Option A (Save on Next)
                        if ($applicant_user_id) { // Ensure we have the main user ID
                            $appApplicant = AppApplicant::findOne(['applicant_user_id' => $applicant_user_id]);
                            if (!$appApplicant) {
                                $appApplicant = new AppApplicant(['applicant_user_id' => $applicant_user_id]);
                                // Try to save AppApplicant to get its PK (applicant_id)
                                // We save with validate=false because its own step might not have been completed yet.
                                // Its full validation and attribute setting will happen in its own step or finalSave.
                                if (!$appApplicant->save(false)) {
                                    $isValid = false;
                                    $stepRenderData['message'] = 'Error: Could not initialize applicant sub-record. Work experience cannot be saved yet. ' . Html::errorSummary($appApplicant);
                                    Yii::error("Failed to save new AppApplicant (for work exp) with applicant_user_id: $applicant_user_id. Errors: " . print_r($appApplicant->errors, true));
                                }
                            } elseif (!$appApplicant->applicant_id) {
                                // This case should ideally not happen if findOne worked and record exists.
                                // But if it's an existing model without PK somehow, try saving.
                                if (!$appApplicant->save(false)) {
                                    $isValid = false;
                                    $stepRenderData['message'] = 'Error: Applicant sub-record is incomplete. Work experience cannot be saved yet. ' . Html::errorSummary($appApplicant);
                                    Yii::error("Failed to save existing AppApplicant (for work exp) that lacked an ID, for applicant_user_id: $applicant_user_id. Errors: " . print_r($appApplicant->errors, true));
                                }
                            }

                            // Reload $appApplicant to ensure PK is populated if it was new
                            if ($isValid && $appApplicant->applicant_id) { // Check if appApplicant has an ID now
                                $currentWorkExpData = $session->get($stepSessionKey, []);
                                $newWorkExpRecord = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);
                                $newWorkExpRecord->setAttributes($currentWorkExpData);
                                $newWorkExpRecord->applicant_id = $appApplicant->applicant_id;

                                if ($newWorkExpRecord->save()) {
                                    // Successfully saved to DB, clear from session to prevent re-save by performFinalSave
                                    // and to ensure next 'Next' click on this step creates a new record.
                                    $session->remove($stepSessionKey);
                                    // Optionally, add a success flash message for this specific save
                                    // Yii::$app->session->addFlash('info', 'Work experience entry saved.');
                                } else {
                                    $isValid = false;
                                    $stepRenderData['message'] = 'Work experience data is valid, but failed to save to database: ' . Html::errorSummary($newWorkExpRecord);
                                    Yii::error("Failed to save AppApplicantWorkExp for applicant_id: {$appApplicant->applicant_id}. Errors: " . print_r($newWorkExpRecord->errors, true));
                                    // Keep data in session if save fails, so user doesn't lose it.
                                }
                            } else if ($isValid) { // $appApplicant->applicant_id was not available
                                $isValid = false; // Downgrade $isValid because we couldn't get applicant_id
                                if (empty($stepRenderData['message'])) { // Don't overwrite previous error from appApplicant save
                                    $stepRenderData['message'] = 'Error: Could not retrieve necessary applicant identifier to save work experience.';
                                }
                                Yii::error("Could not obtain valid applicant_id for applicant_user_id: $applicant_user_id to save work experience.");
                                // Keep data in session.
                            }
                        } else { // $applicant_user_id is not available (shouldn't happen if first step is done)
                            $isValid = false;
                            $stepRenderData['message'] = 'Error: Applicant session not found. Cannot save work experience.';
                            Yii::error("applicant_user_id not available when trying to save work experience.");
                            // Keep data in session.
                        }
                    } else { // workExpModel validation failed
                        $isValid = false;
                        $stepRenderData['message'] = Html::errorSummary($workExpModel);
                    }
                } else { // workExpModel->load($postData) failed
                    $isValid = false;
                    $stepRenderData['message'] = 'Could not load work experience data.';
                }
            } elseif ($currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) {
                $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
                // Username and profile image are now handled in STEP_PERSONAL_DETAILS
                // Only password-related fields are handled here.

                if ($model->load($postData) && $model->validate()) {
                    $isValid = true;
                    // Logic for profile_image_file and username removed from here
                } else {
                    $isValid = false;
                    if (empty($stepRenderData['message'])) {
                         $stepRenderData['message'] = 'Please correct the errors in Account Settings. ' . Html::errorSummary($model);
                    }
                }
                if ($isValid) {
                    // Save only relevant attributes for account settings to session
                    $session->set($stepSessionKey, $model->getAttributes(['password', 'change_pass']));
                }
            }

            if ($isValid) {
                if ($action === 'next') {
                    if ($currentStepIndex < count($this->_steps) - 1) {
                        $activeRenderStep = $this->_steps[$currentStepIndex + 1];
                    } else {
                        // Should ideally not happen if 'Next' is hidden on last step, but as a fallback:
                        $activeRenderStep = $currentProcessingStep; // Stay on current if it's the last
                    }
                    $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);

                    if ($request->isAjax) {
                        try {
                            // Destructure all models returned by loadModelsForStep
                            list($nextModel, $nextAppApplicantModel, $nextWorkExpModel) = $this->loadModelsForStep(
                                $activeRenderStep,
                                $applicant_user_id,
                                $session,
                                $wizardDataKeyPrefix
                            );

                            $stepViewFilePath = Yii::getAlias('@app/views/applicant-user/' . $activeRenderStep . '.php');
                            if (!file_exists($stepViewFilePath)) {
                                Yii::error("Wizard step view file not found when preparing next step: {$stepViewFilePath} for step key {$activeRenderStep}");
                                return ['success' => false, 'message' => "Error: The content for step '" . Html::encode($activeRenderStep) . "' is unavailable (view file missing)."];
                            }

                            // Pass all necessary models to renderAjax
                            $viewParams = [
                                'model' => $nextModel,
                                'appApplicantModel' => $nextAppApplicantModel,
                                'workExpModel' => $nextWorkExpModel, // Pass the work experience model
                                'stepData' => [], // Fresh step, so no specific stepData messages yet
                                'steps' => $this->_steps,
                                'currentStepForView' => $activeRenderStep
                            ];

                            $fetchedWorkExperiences = null;
                            if ($activeRenderStep === self::STEP_WORK_EXPERIENCE && $applicant_user_id) {
                                $appUser = AppApplicantUser::findOne($applicant_user_id);
                                if ($appUser && $appUser->appApplicant) {
                                    $fetchedWorkExperiences = AppApplicantWorkExp::find()
                                        ->where(['applicant_id' => $appUser->appApplicant->applicant_id])
                                        ->orderBy(['year_from' => SORT_DESC])
                                        ->asArray()
                                        ->all();
                                    $viewParams['existingWorkExperiences'] = $fetchedWorkExperiences;
                                }
                            }

                            $jsonResponse = ['success' => true, 'html' => $this->renderAjax($activeRenderStep, $viewParams), 'nextStep' => $activeRenderStep, 'applicant_user_id' => $applicant_user_id];

                            // Add personal names to JSON response if next step is work experience, for auto-fill
                            if ($activeRenderStep === self::STEP_WORK_EXPERIENCE) {
                                $personalDetailsFromSession = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
                                $jsonResponse['personalNames'] = [
                                    'firstName' => $personalDetailsFromSession['first_name'] ?? '',
                                    'surname' => $personalDetailsFromSession['surname'] ?? '',
                                ];
                                // Also include existingWorkExperiences in jsonResponse if JS needs it separately
                                // (it's now also available to the view via $viewParams)
                                if ($fetchedWorkExperiences !== null) {
                                    $jsonResponse['existingWorkExperiences'] = $fetchedWorkExperiences;
                                }
                            }
                            return $jsonResponse;
                        } catch (NotFoundHttpException $e) {
                            Yii::error("NotFoundHttpException while preparing AJAX content for step '{$activeRenderStep}', applicant ID '{$applicant_user_id}': " . $e->getMessage());
                            return ['success' => false, 'message' => "Error: Could not load data for the next step. The requested applicant record (ID: " . Html::encode($applicant_user_id) . ") may not exist. Please restart the wizard."];
                        } catch (\Throwable $e) {
                            Yii::error("General exception while preparing next step '{$activeRenderStep}' for applicant ID '{$applicant_user_id}': " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                            return ['success' => false, 'message' => "An unexpected error occurred while preparing the next step ('" . Html::encode($activeRenderStep) . "'). Please try again or contact support."];
                        } // *** ADDED TRY-CATCH BLOCK END ***
                    }
                } elseif ($action === 'save' && $currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) {
                    $finalSaveResult = $this->performFinalSave($applicant_user_id, $session, $wizardDataKeyPrefix);
                    if ($finalSaveResult['success']) {
                        // MODIFIED RESPONSE for successful save
                        if ($request->isAjax) {
                            return [
                                'success' => true,
                                'completed' => true,
                                'message' => 'Your details have been saved successfully!',
                                'applicant_user_id' => $applicant_user_id
                                // Removed 'redirectUrl'
                            ];
                        }
                        // Fallback for non-AJAX, though wizard is primarily AJAX
                        Yii::$app->session->setFlash('success', 'Applicant details saved successfully.');
                        return $this->redirect(['view', 'applicant_user_id' => $applicant_user_id]);
                    } else {
                        $activeRenderStep = $currentProcessingStep;
                        $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
                        $messageForUser = $finalSaveResult['message'] ?? $stepRenderData['message'] ?? 'Failed to save details.';
                        if ($request->isAjax) return ['success' => false, 'errors' => $finalSaveResult['errors'], 'message' => $messageForUser];
                        $stepRenderData['message'] = $messageForUser;
                    }
                } else {
                    $activeRenderStep = $currentProcessingStep;
                    $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
                }
            } else {
                $activeRenderStep = $currentProcessingStep;
                $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);

                if ($request->isAjax) {
                    $errors = [];
                    if ($currentProcessingStep === self::STEP_PERSONAL_DETAILS || $currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) {
                        $errors = $model->getErrors();
                    } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                        // $appApplicantModel might not be initialized if POST is for another step.
                        // This section assumes $appApplicantModel is the one being validated.
                        // If $appApplicantModel was loaded from $postData:
                        if (isset($appApplicantModel) && $appApplicantModel->load($postData)) $errors = $appApplicantModel->getErrors();
                        else $errors = ['applicant_specifics' => 'Could not load data.'];
                    } elseif ($currentProcessingStep === self::STEP_WORK_EXPERIENCE) {
                        // $workExpModel is initialized and validated within its POST block.
                        // $errors should be from that $workExpModel instance.
                        // The $workExpModel from the loop start might be null or a different instance.
                        // This part of the code is reached if $isValid is false.
                        // The $workExpModel that failed validation was local to the STEP_WORK_EXPERIENCE block.
                        // We need to re-fetch or ensure it's available.
                        // For simplicity, message is usually enough. If field-specific errors are needed:
                        $tempWorkExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);
                        $tempWorkExpModel->load($postData); // Load again to get errors
                        $tempWorkExpModel->validate();
                        $errors = $tempWorkExpModel->getErrors();
                    }
                    return ['success' => false, 'errors' => $errors, 'message' => $stepRenderData['message'] ?? 'Validation failed.'];
                }
            }

            if (!$request->isAjax) {
                // Non-AJAX POST should redirect to the current (or next if valid) step.
                // This ensures the URL reflects the current state.
                return $this->redirect(['update-wizard', 'currentStep' => $activeRenderStep, 'applicant_user_id' => $applicant_user_id]);
            }
            // If AJAX and POST was to save (last step) or an error occurred, $activeRenderStep is set.
            // The AJAX response handles success/failure. If it was a 'next' action that succeeded,
            // $activeRenderStep is the next step, and the response includes HTML for it.
            // If it failed validation, $activeRenderStep is the current step, response has errors.
            // $currentStep variable for view rendering at the end should reflect $activeRenderStep.
            $currentStep = $activeRenderStep; // Update $currentStep for potential non-AJAX fallback or if logic continues

        } elseif ($request->isAjax && $request->isGet) { // Handle AJAX GET for loading step content
            $targetStep = $requestedStepInUrl ?? $currentStep; // Step to load from URL or current session step
            if (!in_array($targetStep, $this->_steps)) $targetStep = self::STEP_PERSONAL_DETAILS;

            if (!$applicant_user_id && $targetStep !== self::STEP_PERSONAL_DETAILS) {
                return ['success' => false, 'message' => 'Please complete the first step to obtain an Applicant ID.', 'redirectToStep' => self::STEP_PERSONAL_DETAILS];
            }

            $session->set($wizardDataKeyPrefix . 'current_step', $targetStep);
            // Load all models needed for the target step
            list($renderModel, $renderAppApplicantModel, $renderWorkExpModel) = $this->loadModelsForStep(
                $targetStep,
                $applicant_user_id,
                $session,
                $wizardDataKeyPrefix
                // Pass currently loaded $model as $existingModel if appropriate, though loadModelsForStep handles fetching/newing.
            );

            $viewParams = [
                'model' => $renderModel,
                'appApplicantModel' => $renderAppApplicantModel,
                'workExpModel' => $renderWorkExpModel, // Pass work experience model
                'stepData' => [], // No specific messages for a fresh GET load of a step
                'steps' => $this->_steps,
                'currentStepForView' => $targetStep
            ];

            $fetchedWorkExperiences = null;
            if ($targetStep === self::STEP_WORK_EXPERIENCE && $applicant_user_id) {
                $appUser = AppApplicantUser::findOne($applicant_user_id);
                if ($appUser && $appUser->appApplicant) {
                    $fetchedWorkExperiences = AppApplicantWorkExp::find()
                        ->where(['applicant_id' => $appUser->appApplicant->applicant_id])
                        ->orderBy(['year_from' => SORT_DESC])
                        ->asArray()
                        ->all();
                    $viewParams['existingWorkExperiences'] = $fetchedWorkExperiences;
                }
            }

            $jsonResponse = ['success' => true, 'html' => $this->renderAjax($targetStep, $viewParams), 'currentStep' => $targetStep, 'applicant_user_id' => $applicant_user_id];

            // Add personal names to JSON response if target step is work experience, for auto-fill
            if ($targetStep === self::STEP_WORK_EXPERIENCE) {
                $personalDetailsFromSession = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
                $jsonResponse['personalNames'] = [
                    'firstName' => $personalDetailsFromSession['first_name'] ?? '',
                    'surname' => $personalDetailsFromSession['surname'] ?? '',
                ];
                // Also include existingWorkExperiences in jsonResponse if JS needs it separately
                if ($fetchedWorkExperiences !== null) {
                    $jsonResponse['existingWorkExperiences'] = $fetchedWorkExperiences;
                }
            }
            return $jsonResponse;
        } else {
            // Initial non-AJAX page load or if JS is disabled
            $session->set($wizardDataKeyPrefix . 'current_step', $currentStep);
        }

        // For initial page load or non-AJAX POST fallback: Load all models for the current step
        list($model, $appApplicantModel, $workExpModel) = $this->loadModelsForStep(
            $currentStep,
            $applicant_user_id,
            $session,
            $wizardDataKeyPrefix,
            $model, // Pass the existing $model instance
            isset($appApplicantModel) ? $appApplicantModel : null // Pass existing $appApplicantModel if it was initialized
        );


        // Set scenarios for models based on the current step for initial rendering
        if ($currentStep === self::STEP_PERSONAL_DETAILS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
        } elseif ($currentStep === self::STEP_ACCOUNT_SETTINGS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
        } elseif ($currentStep === self::STEP_WORK_EXPERIENCE && isset($workExpModel)) {
            $workExpModel->scenario = AppApplicantWorkExp::SCENARIO_WIZARD;
        }
        // Add similar for AppApplicant if it has scenarios for its step.

        // Prepare personal names for JS auto-fill if available
        $personalNamesForJs = null;
        $personalDetailsSessionData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
        if (!empty($personalDetailsSessionData['first_name']) || !empty($personalDetailsSessionData['surname'])) {
            $personalNamesForJs = [
                'firstName' => $personalDetailsSessionData['first_name'] ?? '',
                'surname' => $personalDetailsSessionData['surname'] ?? '',
            ];
        }

        $renderParams = [
            'currentStep' => $currentStep,
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
            'workExpModel' => $workExpModel, // Pass work experience model to the main wizard view
            'stepData' => $stepRenderData,
            'steps' => $this->_steps,
            'personalNamesForJs' => $personalNamesForJs, // Pass names for JS
        ];

        // For initial page load, if the current step is work experience, fetch existing experiences
        if ($currentStep === self::STEP_WORK_EXPERIENCE && $applicant_user_id) {
            $appUser = AppApplicantUser::findOne($applicant_user_id);
            if ($appUser && $appUser->appApplicant && $appUser->appApplicant->applicant_id) { // Ensure applicant_id exists
                $renderParams['existingWorkExperiences'] = AppApplicantWorkExp::find()
                    ->where(['applicant_id' => $appUser->appApplicant->applicant_id])
                    ->orderBy(['year_from' => SORT_DESC])
                    ->asArray()
                    ->all();
            }
        }
        return $this->render('update-wizard', $renderParams);
    }

    protected function loadModelsForStep($step, $applicant_user_id, $session, $wizardDataKeyPrefix, $existingModel = null, $existingAppApplicantModel = null)
    {
        // Ensure $existingModel is the correct AppApplicantUser instance
        if ($existingModel instanceof AppApplicantUser) {
            if ($applicant_user_id && $existingModel->applicant_user_id != $applicant_user_id) {
                // If ID mismatch, or if $existingModel was new but we now have an ID, refetch.
                $model = $this->findModel($applicant_user_id);
            } else {
                $model = $existingModel;
            }
        } else {
            $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        }

        // Handle AppApplicant model
        if ($existingAppApplicantModel instanceof AppApplicant) {
            $appApplicantModel = $existingAppApplicantModel;
        } else {
            $appApplicantModel = ($applicant_user_id && $model->appApplicant) ? $model->appApplicant : new AppApplicant();
        }
        if ($applicant_user_id && $model->isNewRecord == false && !$appApplicantModel->applicant_user_id) { // if AppApplicantUser exists, ensure AppApplicant is linked
            $appApplicantModel->applicant_user_id = $model->applicant_user_id;
        }


        // Initialize WorkExpModel - always new for this method, session data populates it.
        $workExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);


        if ($applicant_user_id && !$appApplicantModel->applicant_user_id && $model && !$model->isNewRecord) {
            $appApplicantModel->applicant_user_id = $model->applicant_user_id;
        }


        // Load data from session for the *specific step being loaded*
        // This was changed to load only current step's data into its model
        // For general loading, we load based on $step parameter
        $dataForThisStep = $session->get($wizardDataKeyPrefix . 'data_step_' . $step, []);
        if (!empty($dataForThisStep)) {
            if ($step === self::STEP_PERSONAL_DETAILS) {
                $model->setAttributes($dataForThisStep, false);
            } elseif ($step === self::STEP_APPLICANT_SPECIFICS) {
                $appApplicantModel->setAttributes($dataForThisStep, false);
            } elseif ($step === self::STEP_ACCOUNT_SETTINGS) {
                $model->setAttributes($dataForThisStep, false);
            } elseif ($step === self::STEP_WORK_EXPERIENCE) {
                $workExpModel->setAttributes($dataForThisStep, false);
            }
        }

        // Always return all models that any step might need.
        return [$model, $appApplicantModel, $workExpModel];
    }

    protected function performFinalSave($applicant_user_id, $session, $wizardDataKeyPrefix)
    {
        if (empty($applicant_user_id)) {
            return ['success' => false, 'message' => 'Applicant User ID is missing. Cannot save.', 'errors' => []];
        }

        $finalModel = $this->findModel($applicant_user_id);
        $finalAppApplicantModel = $finalModel->getAppApplicant()->one() ?? new AppApplicant();
        $finalAppApplicantModel->applicant_user_id = $applicant_user_id; // Ensure FK is set

        // Load data from session for each step
        // Personal details data is already part of $finalModel through findModel and subsequent step loads if any.
        // $personalDetailsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
        // $finalModel->setAttributes($personalDetailsData, false); // Already handled by session loads for current step or direct load

        $applicantSpecificsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_APPLICANT_SPECIFICS, []);
        $accountSettingsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_ACCOUNT_SETTINGS, []);
        $workExpData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_WORK_EXPERIENCE, []);

        // Set attributes for AppApplicantUser from Account Settings
        // Personal details attributes should already be on $finalModel from its step or initial load
        $finalModel->setAttributes($accountSettingsData, false); // Overwrites only fields in accountSettingsData
        $finalModel->scenario = AppApplicantUser::SCENARIO_DEFAULT; // Set to default for full validation before save

        // Set attributes for AppApplicant from its step data
        $finalAppApplicantModel->setAttributes($applicantSpecificsData, false);
        // $finalAppApplicantModel->scenario = AppApplicant::SCENARIO_DEFAULT; // If AppApplicant has scenarios

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($finalModel->save()) { // Save AppApplicantUser
                // Important: Ensure applicant_user_id is set on finalAppApplicantModel if it's new
                if ($finalAppApplicantModel->isNewRecord) {
                    $finalAppApplicantModel->applicant_user_id = $finalModel->applicant_user_id;
                }

                if ($finalAppApplicantModel->save()) { // Save AppApplicant
                    // Now handle AppApplicantWorkExp
                    if (!empty($workExpData)) {
                        $finalWorkExpModel = AppApplicantWorkExp::findOne(['applicant_id' => $finalAppApplicantModel->applicant_id]);
                        if (!$finalWorkExpModel) {
                            $finalWorkExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);
                        }
                        $finalWorkExpModel->setAttributes($workExpData, false);
                        $finalWorkExpModel->applicant_id = $finalAppApplicantModel->applicant_id; // Link to AppApplicant

                        if (!$finalWorkExpModel->save()) {
                            $transaction->rollBack();
                            Yii::error($finalWorkExpModel->errors);
                            return ['success' => false, 'message' => 'Error saving work experience: ' . Html::errorSummary($finalWorkExpModel), 'errors' => $finalWorkExpModel->getErrors()];
                        }
                    }

                    $transaction->commit();
                    // Clear session data for all steps after successful save
                    foreach ($this->_steps as $s) {
                        $session->remove($wizardDataKeyPrefix . 'data_step_' . $s);
                    }
                    $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                    $session->remove($wizardDataKeyPrefix . 'current_step');
                    return ['success' => true];
                } else { // AppApplicant save failed
                    $transaction->rollBack();
                    Yii::error($finalAppApplicantModel->errors);
                    return ['success' => false, 'message' => 'Error saving applicant specifics: ' . Html::errorSummary($finalAppApplicantModel), 'errors' => $finalAppApplicantModel->getErrors()];
                }
            } else { // AppApplicantUser save failed
                $transaction->rollBack();
                Yii::error($finalModel->errors);
                return ['success' => false, 'message' => 'Error saving applicant user details: ' . Html::errorSummary($finalModel), 'errors' => $finalModel->getErrors()];
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error("Exception during final save for applicant_user_id {$applicant_user_id}: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => 'An unexpected error occurred while saving: ' . $e->getMessage(), 'errors' => []];
        }
    }

    public function actionIndex()
    {
        $searchModel = new AppApplicantUserSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

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

    protected function findModel($applicant_user_id)
    {
        if (($model = AppApplicantUser::findOne(['applicant_user_id' => $applicant_user_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
