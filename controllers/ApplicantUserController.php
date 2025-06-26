<?php

namespace app\controllers;

use Yii;
use app\models\AppApplicantUser;
use app\models\AppApplicantEducation; // Added for the new step
use yii\web\Controller;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;
use app\models\search\AppApplicantUserSearch;
use yii\web\NotFoundHttpException;
use app\models\AppApplicant;
use yii\helpers\ArrayHelper;
use yii\helpers\Html; // Added for Html::errorSummary
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class ApplicantUserController extends Controller
{
    const STEP_PERSONAL_DETAILS = 'personal-details';
    const STEP_APPLICANT_SPECIFICS = 'applicant-specifics';
    const STEP_ACCOUNT_SETTINGS = 'account-settings';
    const STEP_EDUCATION = 'education'; // New step

    private $_steps = [
        self::STEP_PERSONAL_DETAILS,
        self::STEP_APPLICANT_SPECIFICS,
        self::STEP_EDUCATION, // New step added
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
        $appApplicantModel = ($applicant_user_id && $model->appApplicant) ? $model->appApplicant : new AppApplicant();
        if ($applicant_user_id && !$appApplicantModel->applicant_user_id) {
            $appApplicantModel->applicant_user_id = $applicant_user_id;
        }

        $stepRenderData = ['message' => null];
        $activeRenderStep = $currentStep;

        if ($request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        }

        if ($request->isPost) {
            $postData = $request->post();
            $currentProcessingStep = $postData['current_step_validated'] ?? $session->get($wizardDataKeyPrefix . 'current_step', self::STEP_PERSONAL_DETAILS);
            if (!in_array($currentProcessingStep, $this->_steps)) $currentProcessingStep = self::STEP_PERSONAL_DETAILS;

            $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $currentProcessingStep;
            $isValid = false;

            if (isset($postData['wizard_cancel'])) {
                foreach ($this->_steps as $s) { $session->remove($wizardDataKeyPrefix . 'data_step_' . $s); }
                $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                $session->remove($wizardDataKeyPrefix . 'current_step');
                if ($request->isAjax) return ['success' => true, 'cancelled' => true, 'redirectUrl' => \yii\helpers\Url::to(['index'])];
                Yii::$app->session->setFlash('info', 'Wizard cancelled.');
                return $this->redirect(['index']);
            }

            $currentStepIndex = array_search($currentProcessingStep, $this->_steps);
            $action = '';
            if (isset($postData['wizard_next'])) $action = 'next';
            elseif (isset($postData['wizard_save'])) $action = 'save';

            if ($currentProcessingStep === self::STEP_PERSONAL_DETAILS) {
                $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
                if ($model->load($postData) && $model->validate()) {
                    $session->set($stepSessionKey, $model->getAttributes());
                    $isValid = true;
                    if ($model->isNewRecord) {
                        if ($model->save(false)) {
                            $applicant_user_id = $model->applicant_user_id;
                            $session->set($wizardDataKeyPrefix . 'applicant_user_id', $applicant_user_id);
                            $appApplicantModel->applicant_user_id = $applicant_user_id;
                        } else { $isValid = false; $stepRenderData['message'] = 'Failed to save personal details.'; Yii::error($model->errors); }
                    } else {
                        if (!$model->save(false)) { $isValid = false; $stepRenderData['message'] = 'Failed to update personal details.'; Yii::error($model->errors); }
                    }
                } else { $isValid = false; if(empty($stepRenderData['message'])) $stepRenderData['message'] = 'Please correct errors in Personal Details.'; }
            } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                if ($appApplicantModel->load($postData) && $appApplicantModel->validate()) {
                    $session->set($stepSessionKey, $appApplicantModel->getAttributes());
                    $isValid = true;
                } else { $isValid = false; if(empty($stepRenderData['message'])) $stepRenderData['message'] = 'Please correct errors in Applicant Specifics.'; }
            } elseif ($currentProcessingStep === self::STEP_EDUCATION) {
                // Ensure appApplicantModel has applicant_id. This step should ideally follow STEP_APPLICANT_SPECIFICS.
                if (!$appApplicantModel->applicant_id) {
                    // Attempt to load/save AppApplicant from session if not already done
                    $appApplicantDataFromSession = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_APPLICANT_SPECIFICS, []);
                    if ($appApplicantDataFromSession) {
                        $appApplicantModel->setAttributes($appApplicantDataFromSession, false);
                        // Critical: If AppApplicant is new, it needs to be saved to get an applicant_id
                        // This assumes that by the time we reach education, AppApplicantUser (and thus applicant_user_id) is already saved.
                        if ($appApplicantModel->isNewRecord && $applicant_user_id) {
                             $appApplicantModel->applicant_user_id = $applicant_user_id; // Ensure it's set
                            if (!$appApplicantModel->save()) {
                                $isValid = false;
                                $stepRenderData['message'] = 'Error preparing education step: Could not save applicant specifics. ' . Html::errorSummary($appApplicantModel);
                                Yii::error($appApplicantModel->errors);
                            }
                        } else if (!$appApplicantModel->applicant_user_id && $applicant_user_id) {
                            // If not new, but somehow applicant_user_id is not set (should not happen if logic is correct)
                            $appApplicantModel->applicant_user_id = $applicant_user_id;
                        }
                    }
                     if (!$appApplicantModel->applicant_id && $isValid) { // Check $isValid again in case save failed
                        $isValid = false;
                        $stepRenderData['message'] = 'Cannot proceed to Education Details: Applicant ID is missing. Please complete previous steps.';
                    }
                }

                // Initialize AppApplicantEducation model
                // Assuming one education record for now in the wizard. If multiple, this needs rethinking.
                // Try to find existing education record for this applicant_id or create new
                $educationModel = AppApplicantEducation::findOne(['applicant_id' => $appApplicantModel->applicant_id]);
                if ($educationModel === null) {
                    $educationModel = new AppApplicantEducation(['applicant_id' => $appApplicantModel->applicant_id]);
                }
                // If $appApplicantModel->applicant_id is still null here, $educationModel will not have it set.
                // The check above should prevent this if $isValid is false.

                if ($isValid && $educationModel->load($postData)) { // Check $isValid before loading
                    $educationModel->applicant_id = $appApplicantModel->applicant_id; // Ensure applicant_id is set
                    if ($educationModel->validate()) {
                        $session->set($stepSessionKey, $educationModel->getAttributes());
                        $isValid = true;
                    } else {
                        $isValid = false;
                        if(empty($stepRenderData['message'])) $stepRenderData['message'] = 'Please correct errors in Education Details.';
                        Yii::error($educationModel->errors);
                    }
                } elseif($isValid) { // $isValid was true, but load failed
                    $isValid = false;
                    $stepRenderData['message'] = 'Could not load education details data.';
                }
                // If $isValid was already false from appApplicantModel issues, it remains false.

            } elseif ($currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) {
                $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
                $model->profile_image_file = UploadedFile::getInstance($model, 'profile_image_file');
                $oldProfileImage = $model->profile_image;

                if ($model->load($postData)) {
                    if (!$model->profile_image_file) {
                         $model->profile_image = $oldProfileImage;
                    }
                    if ($model->validate()) {
                        $isValid = true;
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
                                $model->profile_image = $uniqueFilename;
                            } else {
                                $isValid = false;
                                $model->addError('profile_image_file', 'Could not save the uploaded image.');
                                $stepRenderData['message'] = 'Error saving profile image. Please try again or contact support.';
                            }
                        }
                    } else {
                        $isValid = false;
                        if ($model->hasErrors('profile_image_file')) {
                            $stepRenderData['message'] = 'Profile image error: ' . $model->getFirstError('profile_image_file');
                        } elseif (empty($stepRenderData['message'])) {
                            $stepRenderData['message'] = 'Please correct the errors in Account Settings.';
                        }
                    }
                } else {
                    $isValid = false;
                    $stepRenderData['message'] = 'Could not load account settings data. Please try again.';
                }
                if ($isValid) {
                    $session->set($stepSessionKey, $model->getAttributes(['username', 'password', 'profile_image', 'change_pass']));
                }
            }

            if ($isValid) {
                if ($action === 'next') {
                    if ($currentStepIndex < count($this->_steps) - 1) {
                        $activeRenderStep = $this->_steps[$currentStepIndex + 1];
                    } else {
                        $activeRenderStep = $currentProcessingStep;
                    }
                    $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
                    if ($request->isAjax) {
                        try { // *** ADDED TRY-CATCH BLOCK START ***
                            list($nextModel, $nextAppApplicantModel, $nextEducationModel) = $this->loadModelsForStep(
                                $activeRenderStep,
                                $applicant_user_id,
                                $session,
                                $wizardDataKeyPrefix
                            );

                            $stepViewFilePath = Yii::getAlias('@app/views/applicant-user/' . $activeRenderStep . '.php');
                            if (!file_exists($stepViewFilePath)) {
                                 Yii::error("Wizard step view file not found when preparing next step: {$stepViewFilePath} for step key {$activeRenderStep}");
                                 return ['success' => false, 'message' => "Error: The content for step '".Html::encode($activeRenderStep)."' is unavailable (view file missing)."];
                            }

                            $html = $this->renderAjax($activeRenderStep, [
                                'model' => $nextModel,
                                'appApplicantModel' => $nextAppApplicantModel,
                                'educationModel' => $nextEducationModel, // Pass education model
                                'stepData' => [],
                                'steps' => $this->_steps,
                                'currentStepForView' => $activeRenderStep
                            ]);
                            return ['success' => true, 'html' => $html, 'nextStep' => $activeRenderStep, 'applicant_user_id' => $applicant_user_id];

                        } catch (NotFoundHttpException $e) {
                            Yii::error("NotFoundHttpException while preparing next step '{$activeRenderStep}' for applicant ID '{$applicant_user_id}': " . $e->getMessage());
                            return ['success' => false, 'message' => "Error: Could not load data for the next step. The requested applicant record (ID: ".Html::encode($applicant_user_id).") may not exist. Please restart the wizard."];
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
                        $errors = $appApplicantModel->getErrors();
                    } elseif ($currentProcessingStep === self::STEP_EDUCATION) {
                        // $educationModel should have been initialized in the POST processing block for this step
                        // Need to re-initialize it here if not already available in this scope,
                        // or ensure it's passed/available. For simplicity, let's assume it was set.
                        // This requires $educationModel to be defined in the outer scope of the POST block.
                        // Let's adjust the POST block to ensure $educationModel is defined.
                        // For now, assuming $educationModel is accessible here:
                        if (isset($educationModel) && $educationModel instanceof AppApplicantEducation) {
                            $errors = $educationModel->getErrors();
                        } else {
                            // Fallback or error if $educationModel wasn't set as expected
                            // This might happen if $isValid was false before education model processing
                            // For example, if $appApplicantModel->applicant_id was missing.
                            // In such cases, the specific error message in $stepRenderData['message'] is more important.
                            $errors = [];
                        }
                    }
                    return ['success' => false, 'errors' => $errors, 'message' => $stepRenderData['message'] ?? 'Validation failed.'];
                }
            }

            if (!$request->isAjax) {
                return $this->redirect(['update-wizard', 'currentStep' => $activeRenderStep, 'applicant_user_id' => $applicant_user_id]);
            }
            $currentStep = $activeRenderStep;

        } elseif ($request->isAjax && $request->isGet) {
            $targetStep = $requestedStepInUrl ?? $currentStep;
            if (!in_array($targetStep, $this->_steps)) $targetStep = self::STEP_PERSONAL_DETAILS;

            if (!$applicant_user_id && $targetStep !== self::STEP_PERSONAL_DETAILS) {
                 return ['success' => false, 'message' => 'Please complete the first step.', 'redirectToStep' => self::STEP_PERSONAL_DETAILS];
            }

            $session->set($wizardDataKeyPrefix . 'current_step', $targetStep);
            list($renderModel, $renderAppApplicantModel, $renderEducationModel) = $this->loadModelsForStep($targetStep, $applicant_user_id, $session, $wizardDataKeyPrefix);

            $html = $this->renderAjax($targetStep, [
                'model' => $renderModel,
                'appApplicantModel' => $renderAppApplicantModel,
                'educationModel' => $renderEducationModel, // Pass education model
                'stepData' => [],
                'steps' => $this->_steps,
                'currentStepForView' => $targetStep
            ]);
            return ['success' => true, 'html' => $html, 'currentStep' => $targetStep, 'applicant_user_id' => $applicant_user_id];
        } else {
            $session->set($wizardDataKeyPrefix . 'current_step', $currentStep);
        }

        // Load models for the initial non-AJAX page load or if not AJAX POST/GET
        list($model, $appApplicantModel, $educationModel) = $this->loadModelsForStep(
            $currentStep,
            $applicant_user_id,
            $session,
            $wizardDataKeyPrefix,
            isset($model) ? $model : null, // Pass existing $model if already initialized
            isset($appApplicantModel) ? $appApplicantModel : null // Pass existing $appApplicantModel
        );


        if ($currentStep === self::STEP_PERSONAL_DETAILS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
        } elseif ($currentStep === self::STEP_ACCOUNT_SETTINGS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
        }

        return $this->render('update-wizard', [
            'currentStep' => $currentStep,
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
            'educationModel' => $educationModel, // Pass education model
            'stepData' => $stepRenderData,
            'steps' => $this->_steps,
        ]);
    }

    protected function loadModelsForStep($step, $applicant_user_id, $session, $wizardDataKeyPrefix, $existingModel = null, $existingAppApplicantModel = null)
    {
        $model = $existingModel ?? ($applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser());
        $appApplicantModel = $existingAppApplicantModel ?? (($applicant_user_id && $model->appApplicant) ? $model->appApplicant : new AppApplicant());
        $educationModel = null; // Initialize

        if ($applicant_user_id && !$appApplicantModel->applicant_user_id) {
            $appApplicantModel->applicant_user_id = $applicant_user_id;
        }

        // Special handling for $appApplicantModel to ensure it has an applicant_id if possible,
        // especially before loading the education step.
        if ($appApplicantModel->isNewRecord && $appApplicantModel->applicant_user_id && !$appApplicantModel->applicant_id) {
            // Try to load attributes from session if STEP_APPLICANT_SPECIFICS data exists
            // This is to make sure $appApplicantModel is as complete as possible if it hasn't been saved yet.
            $appSpecificsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_APPLICANT_SPECIFICS, []);
            if ($appSpecificsData) {
                $appApplicantModel->setAttributes($appSpecificsData, false);
            }
        }


        $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $step;
        $stepDataFromSession = $session->get($stepSessionKey, []);

        if (!empty($stepDataFromSession)) {
            if ($step === self::STEP_PERSONAL_DETAILS) {
                $model->setAttributes($stepDataFromSession, false);
            } elseif ($step === self::STEP_APPLICANT_SPECIFICS) {
                $appApplicantModel->setAttributes($stepDataFromSession, false);
            } elseif ($step === self::STEP_EDUCATION) {
                if ($appApplicantModel->applicant_id) {
                    $educationModel = AppApplicantEducation::findOne(['applicant_id' => $appApplicantModel->applicant_id]);
                    if (!$educationModel) {
                        $educationModel = new AppApplicantEducation(['applicant_id' => $appApplicantModel->applicant_id]);
                    }
                    $educationModel->setAttributes($stepDataFromSession, false);
                } else {
                    // $appApplicantModel->applicant_id is not available.
                    // Create a new education model, it will lack applicant_id.
                    // The view and POST handling for education step must be aware of this.
                    $educationModel = new AppApplicantEducation();
                    $educationModel->setAttributes($stepDataFromSession, false); // Data loaded, but no applicant_id yet.
                    Yii::warning("Loading Education step: AppApplicant ID is not yet available. Session data loaded into Education model might be incomplete.");
                }
            } elseif ($step === self::STEP_ACCOUNT_SETTINGS) {
                $model->setAttributes($stepDataFromSession, false);
            }
        } elseif ($step === self::STEP_EDUCATION) { // No session data, but it's the education step
            if ($appApplicantModel->applicant_id) {
                $educationModel = AppApplicantEducation::findOne(['applicant_id' => $appApplicantModel->applicant_id]);
                if (!$educationModel) {
                    $educationModel = new AppApplicantEducation(['applicant_id' => $appApplicantModel->applicant_id]);
                }
            } else {
                // No session data and no applicant_id for a new education model.
                $educationModel = new AppApplicantEducation();
                 Yii::warning("Initializing new Education step: AppApplicant ID is not yet available.");
            }
        }
        // Return all three models. $educationModel will be null if not the education step or if it couldn't be initialized.
        return [$model, $appApplicantModel, $educationModel];
    }

    protected function performFinalSave($applicant_user_id, $session, $wizardDataKeyPrefix)
    {
        if (empty($applicant_user_id)) {
            return ['success' => false, 'message' => 'Applicant User ID is missing. Cannot save.', 'errors' => []];
        }

        $finalModel = $this->findModel($applicant_user_id); // AppApplicantUser
        $finalAppApplicantModel = $finalModel->getAppApplicant()->one(); // AppApplicant

        $personalDetailsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []); // Already applied to $finalModel by findModel potentially
        $applicantSpecificsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_APPLICANT_SPECIFICS, []);
        $educationData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_EDUCATION, []);
        $accountSettingsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_ACCOUNT_SETTINGS, []);

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // 1. Save AppApplicantUser details (Personal Details, Account Settings)
            // Personal details are part of $finalModel already. Account settings from session.
            if (!empty($accountSettingsData)) {
                // Unset profile_image_file if it's in session data to prevent issues with setAttributes
                // Actual file handling is done during step processing. Here we only save the filename.
                unset($accountSettingsData['profile_image_file']);
                $finalModel->setAttributes($accountSettingsData, false);
            }
            $finalModel->scenario = AppApplicantUser::SCENARIO_DEFAULT; // Use default scenario for final save
            if (!$finalModel->save()) {
                $transaction->rollBack();
                Yii::error($finalModel->errors);
                return ['success' => false, 'message' => 'Error saving applicant user details: ' . Html::errorSummary($finalModel), 'errors' => $finalModel->getErrors()];
            }

            // 2. Save AppApplicant details (Applicant Specifics)
            if (empty($finalAppApplicantModel) && !empty($applicantSpecificsData)) {
                $finalAppApplicantModel = new AppApplicant(['applicant_user_id' => $applicant_user_id]);
            }
            if (!empty($applicantSpecificsData)) {
                $finalAppApplicantModel->setAttributes($applicantSpecificsData, false);
            }
            // Ensure applicant_user_id is set if it's a new AppApplicant or was missing
            if ($finalAppApplicantModel && !$finalAppApplicantModel->applicant_user_id) {
                $finalAppApplicantModel->applicant_user_id = $applicant_user_id;
            }

            if ($finalAppApplicantModel && $finalAppApplicantModel->getDirtyAttributes()) { // Save only if there are changes or it's new
                 if (!$finalAppApplicantModel->save()) {
                    $transaction->rollBack();
                    Yii::error($finalAppApplicantModel->errors);
                    return ['success' => false, 'message' => 'Error saving applicant specifics: ' . Html::errorSummary($finalAppApplicantModel), 'errors' => $finalAppApplicantModel->getErrors()];
                }
            } elseif (empty($finalAppApplicantModel) && in_array(self::STEP_APPLICANT_SPECIFICS, $this->_steps) && !empty($applicantSpecificsData)) {
                 // This case implies $applicantSpecificsData existed, new model was made, but it wasn't saved. Should be caught by save() fail.
                 // Or, if $applicantSpecificsData was empty, but the step exists, it's a problem if education step depends on it.
                 // If $finalAppApplicantModel is still null and education data exists, it's an issue.
                 if (!$finalAppApplicantModel && !empty($educationData)) {
                    $transaction->rollBack();
                    return ['success' => false, 'message' => 'Applicant specifics are required to save education details.'];
                 }
            }


            // 3. Save AppApplicantEducation details
            if (!empty($educationData)) {
                if (!$finalAppApplicantModel || !$finalAppApplicantModel->applicant_id) {
                    $transaction->rollBack();
                    Yii::error("Cannot save education details: AppApplicant model or applicant_id is missing.");
                    return ['success' => false, 'message' => 'Error saving education details: Applicant information is incomplete.'];
                }
                // Assuming one education record for the wizard. Find existing or create new.
                $educationModel = AppApplicantEducation::findOne(['applicant_id' => $finalAppApplicantModel->applicant_id]);
                if (!$educationModel) {
                    $educationModel = new AppApplicantEducation(['applicant_id' => $finalAppApplicantModel->applicant_id]);
                }
                $educationModel->setAttributes($educationData, false);
                if (!$educationModel->save()) {
                    $transaction->rollBack();
                    Yii::error($educationModel->errors);
                    return ['success' => false, 'message' => 'Error saving education details: ' . Html::errorSummary($educationModel), 'errors' => $educationModel->getErrors()];
                }
            }

            $transaction->commit();
            foreach ($this->_steps as $s) { $session->remove($wizardDataKeyPrefix . 'data_step_' . $s); }
            $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
            $session->remove($wizardDataKeyPrefix . 'current_step');
            return ['success' => true];

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage() . "\n" . $e->getTraceAsString());
            return ['success' => false, 'message' => 'An unexpected error occurred during final save: ' . $e->getMessage(), 'errors' => []];
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
