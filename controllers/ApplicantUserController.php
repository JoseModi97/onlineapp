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
use app\models\AppApplicantEducation;
use yii\helpers\ArrayHelper;
use yii\helpers\Html; // Added for Html::errorSummary
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class ApplicantUserController extends Controller
{
    const STEP_PERSONAL_DETAILS = 'personal-details';
    const STEP_APPLICANT_SPECIFICS = 'applicant-specifics';
    const STEP_EDUCATION_DETAILS = 'education-details'; // New step
    const STEP_ACCOUNT_SETTINGS = 'account-settings';

    private $_steps = [
        self::STEP_PERSONAL_DETAILS,
        self::STEP_APPLICANT_SPECIFICS,
        self::STEP_EDUCATION_DETAILS, // New step added here
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
        // Initialize $educationModel to null or a new instance
        $educationModel = new AppApplicantEducation();

        if ($applicant_user_id && $appApplicantModel->isNewRecord && $model->appApplicant && $model->appApplicant->applicant_id) {
            $appApplicantModel = $model->appApplicant; // If $model has a linked AppApplicant, use it
        } elseif ($applicant_user_id && $appApplicantModel->isNewRecord && $model->applicant_user_id) {
            // If AppApplicant is still new, try to load it or set its applicant_user_id for creation
            $relatedAppApplicant = AppApplicant::findOne(['applicant_id' => $model->applicant_user_id]);
            if ($relatedAppApplicant) {
                $appApplicantModel = $relatedAppApplicant;
            } else {
                $appApplicantModel->applicant_user_id = $model->applicant_user_id;
            }
        }
        // Now, specifically for the education step, try to load existing or prepare new
        if ($currentStep === self::STEP_EDUCATION_DETAILS) {
            if ($appApplicantModel && $appApplicantModel->applicant_id) {
                $loadedEducationModel = AppApplicantEducation::find()
                    ->where(['applicant_id' => $appApplicantModel->applicant_id])
                    ->orderBy(['education_id' => SORT_DESC])
                    ->one();
                if ($loadedEducationModel) {
                    $educationModel = $loadedEducationModel;
                }
                // Ensure applicant_id is set if we are creating a new education record
                if ($educationModel->isNewRecord) {
                    $educationModel->applicant_id = $appApplicantModel->applicant_id;
                }
            }
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
                } else {
                    $isValid = false;
                    if (empty($stepRenderData['message'])) $stepRenderData['message'] = 'Please correct errors in Personal Details.';
                }
            } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                if ($appApplicantModel->load($postData) && $appApplicantModel->validate()) {
                    $session->set($stepSessionKey, $appApplicantModel->getAttributes());
                    $isValid = true;
                } else {
                    $isValid = false;
                    if (empty($stepRenderData['message'])) $stepRenderData['message'] = 'Please correct errors in Applicant Specifics.';
                }
            } elseif ($currentProcessingStep === self::STEP_EDUCATION_DETAILS) {
                // Load existing or new education model for the step
                // $educationModel is already initialized/loaded at the beginning of actionUpdateWizard
                // and further refined by loadModelsForStep if it's a GET request for the step.
                // For POST, we might need to re-ensure we have the correct one.

                // Re-fetch or ensure $educationModel is correctly scoped for POST processing
                // This logic mirrors parts of loadModelsForStep to ensure $educationModel is the right one to process for POST
                if ($appApplicantModel && $appApplicantModel->applicant_id) {
                    $educationModelToProcess = AppApplicantEducation::find()
                        ->where(['applicant_id' => $appApplicantModel->applicant_id])
                        ->orderBy(['education_id' => SORT_DESC])
                        ->one();
                    if (!$educationModelToProcess) {
                        $educationModelToProcess = new AppApplicantEducation(['applicant_id' => $appApplicantModel->applicant_id]);
                    }
                } else {
                    // This case should ideally not happen if personal details (step 1) are enforced
                    $educationModelToProcess = new AppApplicantEducation();
                    $stepRenderData['message'] = 'Applicant ID is missing. Cannot process education details.';
                    $isValid = false;
                }

                if ($isValid !== false) { // Proceed if applicant_id was found
                    // SET SCENARIO FOR EDUCATION STEP
                    $educationModelToProcess->scenario = AppApplicantEducation::SCENARIO_WIZARD_EDUCATION_STEP;

                    // Handle file upload for education certificate
                    $educationModelToProcess->education_certificate_file = UploadedFile::getInstance($educationModelToProcess, 'education_certificate_file');
                    $oldCertificateFile = $educationModelToProcess->isNewRecord ? null : $educationModelToProcess->file_name;

                    if ($educationModelToProcess->load($postData)) {
                        if (!$educationModelToProcess->education_certificate_file && !$educationModelToProcess->isNewRecord) {
                            // Preserve old file if no new one is uploaded and it's an existing record
                            $educationModelToProcess->file_name = $postData['AppApplicantEducation']['file_name_hidden'] ?? $oldCertificateFile;
                            // Assuming 'file_name_hidden' stores the current file name if no new upload
                        }
                        // Scenario is already set, validate() will use it.
                        if ($educationModelToProcess->validate()) {
                            if ($educationModelToProcess->education_certificate_file) {
                                $uploadPath = Yii::getAlias('@webroot/uploads/education_certificates/');
                                if (!is_dir($uploadPath)) {
                                    FileHelper::createDirectory($uploadPath);
                                }
                                $uniqueFilename = Yii::$app->security->generateRandomString() . '.' . $educationModelToProcess->education_certificate_file->extension;
                                $filePath = $uploadPath . $uniqueFilename;
                                if ($educationModelToProcess->education_certificate_file->saveAs($filePath)) {
                                    if ($oldCertificateFile && $oldCertificateFile !== $uniqueFilename && file_exists($uploadPath . $oldCertificateFile)) {
                                        @unlink($uploadPath . $oldCertificateFile);
                                    }
                                    $educationModelToProcess->file_name = $uniqueFilename;
                                    $educationModelToProcess->file_path = 'uploads/education_certificates/' . $uniqueFilename; // Relative path for DB
                                } else {
                                    $isValid = false;
                                    $educationModelToProcess->addError('education_certificate_file', 'Could not save the uploaded certificate.');
                                    if (empty($stepRenderData['message'])) { // Ensure specific file error message is prioritized
                                        $stepRenderData['message'] = 'Error saving certificate.';
                                    }
                                }
                            }
                            if ($isValid !== false) { // Re-check isValid after potential file error
                                // Explicitly set education_id for session if it's an update to ensure correct model is fetched later
                                $attributesToSave = $educationModelToProcess->getAttributes();
                                if (!$educationModelToProcess->isNewRecord && $educationModelToProcess->education_id) {
                                    $attributesToSave['education_id'] = $educationModelToProcess->education_id;
                                }
                                $session->set($stepSessionKey, $attributesToSave);
                                $isValid = true;
                            }
                        } else {
                            $isValid = false;
                            if (empty($stepRenderData['message'])) { // Check if a more specific message (e.g., file upload) is already set
                                $stepRenderData['message'] = 'Please correct errors in Education Details.';
                            }
                        }
                    } else {
                        $isValid = false;
                        if (empty($stepRenderData['message'])) {
                             $stepRenderData['message'] = 'Could not load education details data.';
                        }
                    }
                }
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
                                return ['success' => false, 'message' => "Error: The content for step '" . Html::encode($activeRenderStep) . "' is unavailable (view file missing)."];
                            }

                            $renderParams = [
                                'model' => $nextModel,
                                'appApplicantModel' => $nextAppApplicantModel,
                                'stepData' => [],
                                'steps' => $this->_steps,
                                'currentStepForView' => $activeRenderStep
                            ];
                            if ($activeRenderStep === self::STEP_EDUCATION_DETAILS) {
                                $renderParams['educationModel'] = $nextEducationModel;
                            }

                            $html = $this->renderAjax($activeRenderStep, $renderParams);
                            return ['success' => true, 'html' => $html, 'nextStep' => $activeRenderStep, 'applicant_user_id' => $applicant_user_id];
                        } catch (NotFoundHttpException $e) {
                            Yii::error("NotFoundHttpException while preparing next step '{$activeRenderStep}' for applicant ID '{$applicant_user_id}': " . $e->getMessage());
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
                        $errors = $appApplicantModel->getErrors();
                    } elseif ($currentProcessingStep === self::STEP_EDUCATION_DETAILS) {
                        // $educationModelToProcess should be in scope from the POST handling block above
                        if (isset($educationModelToProcess)) {
                            $errors = $educationModelToProcess->getErrors();
                        } else {
                            // Fallback if $educationModelToProcess somehow isn't set, though unlikely
                            $errors = ['education_form' => ['There was an issue loading education data.']];
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

            $renderParams = [
                'model' => $renderModel,
                'appApplicantModel' => $renderAppApplicantModel,
                'stepData' => [],
                'steps' => $this->_steps,
                'currentStepForView' => $targetStep
            ];
            if ($targetStep === self::STEP_EDUCATION_DETAILS) {
                $renderParams['educationModel'] = $renderEducationModel;
            }

            $html = $this->renderAjax($targetStep, $renderParams);
            return ['success' => true, 'html' => $html, 'currentStep' => $targetStep, 'applicant_user_id' => $applicant_user_id];
        } else {
            $session->set($wizardDataKeyPrefix . 'current_step', $currentStep);
        }

        list($model, $appApplicantModel, $educationModel) = $this->loadModelsForStep($currentStep, $applicant_user_id, $session, $wizardDataKeyPrefix, $model, $appApplicantModel);

        if ($currentStep === self::STEP_PERSONAL_DETAILS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
        } elseif ($currentStep === self::STEP_EDUCATION_DETAILS) {
            // Add scenario for education model if needed, e.g.
            // if ($educationModel) $educationModel->scenario = AppApplicantEducation::SCENARIO_WIZARD;
        } elseif ($currentStep === self::STEP_ACCOUNT_SETTINGS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
        }

        $renderParams = [
            'currentStep' => $currentStep,
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
            'stepData' => $stepRenderData,
            'steps' => $this->_steps,
        ];
        if ($currentStep === self::STEP_EDUCATION_DETAILS) {
            $renderParams['educationModel'] = $educationModel;
        }
        return $this->render('update-wizard', $renderParams);
    }

    protected function loadModelsForStep($step, $applicant_user_id, $session, $wizardDataKeyPrefix, $existingModel = null, $existingAppApplicantModel = null, $existingEducationModel = null) // Added $existingEducationModel
    {
        $model = $existingModel ?? ($applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser());
        $appApplicantModel = $existingAppApplicantModel ?? (($applicant_user_id && $model->appApplicant) ? $model->appApplicant : new AppApplicant());
        $educationModel = $existingEducationModel; // Use existing if passed

        if ($applicant_user_id && $appApplicantModel->isNewRecord && $model->appApplicant && $model->appApplicant->applicant_id) {
            $appApplicantModel = $model->appApplicant;
        } elseif ($applicant_user_id && $appApplicantModel->isNewRecord && $model->applicant_user_id) {
            // Try to load AppApplicant if not properly linked initially
            $relatedAppApplicant = AppApplicant::findOne(['applicant_id' => $model->applicant_user_id]);
            if ($relatedAppApplicant) {
                $appApplicantModel = $relatedAppApplicant;
            } else {
                $appApplicantModel->applicant_user_id = $model->applicant_user_id; // Set for new AppApplicant
            }
        }


        if ($step === self::STEP_EDUCATION_DETAILS) {
            if (!$educationModel) { // Only fetch if not already provided (e.g. from initial controller load)
                if ($appApplicantModel && $appApplicantModel->applicant_id) {
                    $educationModel = AppApplicantEducation::find()
                        ->where(['applicant_id' => $appApplicantModel->applicant_id])
                        ->orderBy(['education_id' => SORT_DESC]) // Get the most recent one
                        ->one();
                }
                if (!$educationModel) {
                    $educationModel = new AppApplicantEducation();
                }
            }
            // Ensure applicant_id is set on the education model if AppApplicant model is available
            if ($educationModel && $educationModel->isNewRecord && $appApplicantModel && $appApplicantModel->applicant_id) {
                $educationModel->applicant_id = $appApplicantModel->applicant_id;
            }
        }

        $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $step;
        $stepDataFromSession = $session->get($stepSessionKey, []);

        if (!empty($stepDataFromSession)) {
            if ($step === self::STEP_PERSONAL_DETAILS) {
                $model->setAttributes($stepDataFromSession, false);
            } elseif ($step === self::STEP_APPLICANT_SPECIFICS) {
                $appApplicantModel->setAttributes($stepDataFromSession, false);
            } elseif ($step === self::STEP_EDUCATION_DETAILS && $educationModel) {
                $educationModel->setAttributes($stepDataFromSession, false);
                // If education_id is in session data, ensure it's set, useful for updates
                if (isset($stepDataFromSession['education_id']) && $stepDataFromSession['education_id']) {
                    $educationModel->education_id = $stepDataFromSession['education_id'];
                    $educationModel->setIsNewRecord(false); // Crucial for updates
                }
            } elseif ($step === self::STEP_ACCOUNT_SETTINGS) {
                $model->setAttributes($stepDataFromSession, false);
            }
        }
        return [$model, $appApplicantModel, $educationModel]; // Return education model
    }

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
        $educationData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_EDUCATION_DETAILS, []); // New
        $accountSettingsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_ACCOUNT_SETTINGS, []);

        $finalModel->setAttributes($accountSettingsData, false); // Personal details are part of $finalModel already
        $finalAppApplicantModel->setAttributes($applicantSpecificsData, false);
        $finalModel->scenario = AppApplicantUser::SCENARIO_DEFAULT; // Reset scenario for full validation if needed

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Save AppApplicantUser ($finalModel) and AppApplicant ($finalAppApplicantModel) first
            if ($finalModel->save()) {
                $finalAppApplicantModel->applicant_user_id = $finalModel->applicant_user_id; // Ensure link for new AppApplicant
                if ($finalAppApplicantModel->save()) {
                    // Now handle AppApplicantEducation
                    if (!empty($educationData)) {
                        $educationModelToSave = null;
                        if (!empty($educationData['education_id'])) {
                            $educationModelToSave = AppApplicantEducation::findOne([
                                'education_id' => $educationData['education_id'],
                                'applicant_id' => $finalAppApplicantModel->applicant_id // Ensure it belongs to this applicant
                            ]);
                        }

                        if (!$educationModelToSave) {
                            $educationModelToSave = new AppApplicantEducation();
                        }

                        $educationModelToSave->applicant_id = $finalAppApplicantModel->applicant_id; // Crucial link

                        // Unset education_id from $educationData before mass assignment if it was new, to avoid issues
                        // For existing, it's fine as it's part of the model's attributes.
                        // The main risk is if education_id was in session for a "new" record that somehow didn't get saved.
                        // However, load() should handle this fine.

                        $educationModelToSave->load($educationData, ''); // Load without form name

                        // File path and name are already in $educationData from the step processing
                        // No need to handle file moving here, only model attribute saving.

                        if (!$educationModelToSave->save()) {
                            $transaction->rollBack();
                            Yii::error($educationModelToSave->errors);
                            return ['success' => false, 'message' => 'Error saving education details: ' . Html::errorSummary($educationModelToSave), 'errors' => $educationModelToSave->getErrors()];
                        }
                    }
                    // If all saves are successful
                    $transaction->commit();
                    foreach ($this->_steps as $s) {
                        $session->remove($wizardDataKeyPrefix . 'data_step_' . $s);
                    }
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
