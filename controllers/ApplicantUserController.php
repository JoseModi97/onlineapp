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
use app\models\AppApplicantWorkExp;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;

class ApplicantUserController extends Controller
{
    const STEP_PERSONAL_DETAILS = 'personal-details';
    const STEP_WORK_EXPERIENCE = 'applicant-work-exp';
    const STEP_APPLICANT_SPECIFICS = 'applicant-specifics'; // This will be the last step, fields moved to personal-details

    // STEP_ACCOUNT_SETTINGS is removed

    private $_steps = [
        self::STEP_PERSONAL_DETAILS,
        self::STEP_WORK_EXPERIENCE,
        self::STEP_APPLICANT_SPECIFICS, // Now the last step
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

            // Load AppApplicantUser model ($model)
            if ($applicant_user_id) {
                if (!$model || $model->isNewRecord || $model->applicant_user_id != $applicant_user_id) {
                    $model = $this->findModel($applicant_user_id);
                }
            } else {
                $model = new AppApplicantUser();
            }

            // Initialize AppApplicant model ($appApplicantModel)
            // It will be specifically loaded/created in the STEP_PERSONAL_DETAILS block if needed
            // or for other steps like STEP_APPLICANT_SPECIFICS (which is now the last, potentially empty step)
            $appApplicantModel = $model->isNewRecord ? new AppApplicant() : ($model->appApplicant ?? new AppApplicant());
            if (!$model->isNewRecord && $appApplicantModel->isNewRecord) {
                $appApplicantModel->applicant_user_id = $model->applicant_user_id;
            }


            // $workExpModel will be initialized in its own step processing block
            $workExpModel = null;


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
                // Also ensure $appApplicantModel is available and linked if $model exists
                if ($applicant_user_id && !$model->isNewRecord) {
                    $appApplicantModel = $model->appApplicant ?? new AppApplicant(['applicant_user_id' => $applicant_user_id]);
                    if ($appApplicantModel->isNewRecord) {
                         $appApplicantModel->applicant_user_id = $applicant_user_id;
                    }
                } else {
                     $appApplicantModel = new AppApplicant(); // Will be linked if $model is saved and gets an ID
                }

                $modelLoaded = $model->load($postData);
                $appApplicantModelLoaded = $appApplicantModel->load($postData); // Load data for AppApplicant

                $modelValid = $model->validate();
                $appApplicantModelValid = $appApplicantModel->validate();

                if ($modelLoaded && $modelValid && $appApplicantModelLoaded && $appApplicantModelValid) {
                    $isValid = true;
                    // Save AppApplicantUser if it's new or existing
                    if (!$model->save(false)) { // Save without re-validating, already validated
                        $isValid = false;
                        $stepRenderData['message'] = 'Failed to save personal identity details.';
                        Yii::error($model->errors);
                    } else {
                        // If $model was new, it now has an ID. Link $appApplicantModel.
                        if ($model->isNewRecord || !$applicant_user_id) { // Check if $model was new or $applicant_user_id wasn't set before
                             $applicant_user_id = $model->applicant_user_id; // Get the new/confirmed ID
                             $session->set($wizardDataKeyPrefix . 'applicant_user_id', $applicant_user_id);
                             $appApplicantModel->applicant_user_id = $applicant_user_id; // Link $appApplicantModel
                        }
                        // Session data for personal details will now be an array containing both model attributes
                        $session->set($stepSessionKey, [
                            'user' => $model->getAttributes(),
                            'applicant' => $appApplicantModel->getAttributes()
                        ]);
                    }
                } else {
                    $isValid = false;
                    $errorsCombined = array_merge($model->getErrors(), $appApplicantModel->getErrors());
                    $errorSummary = [];
                    foreach ($errorsCombined as $attributeErrors) {
                        foreach ($attributeErrors as $error) {
                            $errorSummary[] = $error;
                        }
                    }
                    $stepRenderData['message'] = 'Please correct errors in Personal Details: ' . implode(' ', $errorSummary);
                }
            } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                // This is now the last step. As per requirements, its fields were moved to Personal Details.
                // This step might be empty or a confirmation/summary. For now, assume it's just a passthrough.
                // If it needs to save something or load data, that logic would go here.
                // For now, we just mark it as valid to allow progression/saving.
                $isValid = true;
                // If this step is meant to be a final review before saving everything,
                // no specific data needs to be saved to session for *this* step itself,
                // as all data is collected in previous steps.
                // $session->set($stepSessionKey, []); // Example if we need to store something empty
            } elseif ($currentProcessingStep === self::STEP_WORK_EXPERIENCE) {
                $workExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);
                if ($workExpModel->load($postData)) {
                    if ($workExpModel->validate()) {
                        $session->set($stepSessionKey, $workExpModel->getAttributes());
                        $isValid = true;
                        // Save-on-next logic for work experience (if still applicable)
                        if ($applicant_user_id) {
                            $appApplicantForWorkExp = AppApplicant::findOne(['applicant_user_id' => $applicant_user_id]);
                            if (!$appApplicantForWorkExp) {
                                // This might occur if personal details (which creates AppApplicant) hasn't been fully processed
                                // or if there's an issue. For now, we'll try to proceed cautiously.
                                // It's better if AppApplicant is guaranteed to exist by performFinalSave or earlier step.
                                // For "save on next", we might need to ensure AppApplicant record exists first.
                                // This part of the logic might need review based on when AppApplicant is created.
                                // For now, assume AppApplicant might not have its own PK (applicant_id) yet if saved via session.
                                 $appApplicantForWorkExp = new AppApplicant(['applicant_user_id' => $applicant_user_id]);
                                 if (!$appApplicantForWorkExp->save(false)) { // Save to get PK if new
                                     $isValid = false;
                                     $stepRenderData['message'] = 'Error: Could not initialize applicant sub-record for work experience. ' . Html::errorSummary($appApplicantForWorkExp);
                                 }
                            }

                            if ($isValid && $appApplicantForWorkExp && $appApplicantForWorkExp->applicant_id) {
                                $currentWorkExpData = $session->get($stepSessionKey, []);
                                $newWorkExpRecord = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);
                                $newWorkExpRecord->setAttributes($currentWorkExpData);
                                $newWorkExpRecord->applicant_id = $appApplicantForWorkExp->applicant_id;

                                if ($newWorkExpRecord->save()) {
                                    $session->remove($stepSessionKey); // Clear from session
                                } else {
                                    $isValid = false;
                                    $stepRenderData['message'] = 'Work experience is valid, but failed to save: ' . Html::errorSummary($newWorkExpRecord);
                                }
                            } elseif ($isValid) {
                                // $isValid is true, but $appApplicantForWorkExp->applicant_id is not available.
                                // This means work experience data is valid but cannot be saved to DB yet.
                                // It will remain in session.
                                Yii::info("Work experience for applicant_user_id {$applicant_user_id} stored in session, AppApplicant record or ID pending final save for DB persistence.");
                            }
                        } else {
                            $isValid = false;
                            $stepRenderData['message'] = 'Error: Applicant session not found for work experience.';
                        }
                    } else {
                        $isValid = false;
                        $stepRenderData['message'] = Html::errorSummary($workExpModel);
                    }
                } else {
                    $isValid = false;
                    $stepRenderData['message'] = 'Could not load work experience data.';
                }
            }
            // Removed STEP_ACCOUNT_SETTINGS block

            if ($isValid) {
                if ($action === 'next') {
                    if ($currentStepIndex < count($this->_steps) - 1) {
                        $activeRenderStep = $this->_steps[$currentStepIndex + 1];
                    } else {
                        $activeRenderStep = $currentProcessingStep;
                    }
                    $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);

                    if ($request->isAjax) {
                        try {
                            list($nextModel, $nextAppApplicantModel, $nextWorkExpModel) = $this->loadModelsForStep(
                                $activeRenderStep,
                                $applicant_user_id,
                                $session,
                                $wizardDataKeyPrefix
                            );

                            $stepViewFilePath = Yii::getAlias('@app/views/applicant-user/' . $activeRenderStep . '.php');
                            if (!file_exists($stepViewFilePath)) {
                                Yii::error("Wizard step view file not found: {$stepViewFilePath} for step {$activeRenderStep}");
                                return ['success' => false, 'message' => "Error: Content for step '" . Html::encode($activeRenderStep) . "' is unavailable."];
                            }

                            $viewParams = [
                                'model' => $nextModel,
                                'appApplicantModel' => $nextAppApplicantModel,
                                'workExpModel' => $nextWorkExpModel,
                                'stepData' => [],
                                'steps' => $this->_steps,
                                'currentStepForView' => $activeRenderStep
                            ];

                            $fetchedWorkExperiences = null;
                            if ($activeRenderStep === self::STEP_WORK_EXPERIENCE && $applicant_user_id) {
                                $appUser = AppApplicantUser::findOne($applicant_user_id);
                                if ($appUser && $appUser->appApplicant) {
                                    $fetchedWorkExperiences = AppApplicantWorkExp::find()
                                        ->where(['applicant_id' => $appUser->appApplicant->applicant_id])
                                        ->orderBy(['year_from' => SORT_DESC])->asArray()->all();
                                    $viewParams['existingWorkExperiences'] = $fetchedWorkExperiences;
                                }
                            }

                            $jsonResponse = ['success' => true, 'html' => $this->renderAjax($activeRenderStep, $viewParams), 'nextStep' => $activeRenderStep, 'applicant_user_id' => $applicant_user_id];

                            if ($activeRenderStep === self::STEP_WORK_EXPERIENCE) {
                                $personalDetailsSessionData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
                                $userAttributes = $personalDetailsSessionData['user'] ?? [];
                                $jsonResponse['personalNames'] = [
                                    'firstName' => $userAttributes['first_name'] ?? '',
                                    'surname' => $userAttributes['surname'] ?? '',
                                ];
                                if ($fetchedWorkExperiences !== null) {
                                    $jsonResponse['existingWorkExperiences'] = $fetchedWorkExperiences;
                                }
                            }
                            return $jsonResponse;
                        } catch (NotFoundHttpException $e) {
                            Yii::error("NotFoundHttpException for step '{$activeRenderStep}', ID '{$applicant_user_id}': " . $e->getMessage());
                            return ['success' => false, 'message' => "Error: Could not load data for next step. Record (ID: " . Html::encode($applicant_user_id) . ") may not exist."];
                        } catch (\Throwable $e) {
                            Yii::error("Exception for step '{$activeRenderStep}', ID '{$applicant_user_id}': " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
                            return ['success' => false, 'message' => "Unexpected error for step ('" . Html::encode($activeRenderStep) . "')."];
                        }
                    }
                } elseif ($action === 'save' && $currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) { // Save is on the new last step
                    $finalSaveResult = $this->performFinalSave($applicant_user_id, $session, $wizardDataKeyPrefix);
                    if ($finalSaveResult['success']) {
                        if ($request->isAjax) {
                            return [
                                'success' => true,
                                'completed' => true,
                                'message' => 'Your details have been saved successfully!',
                                'applicant_user_id' => $applicant_user_id
                            ];
                        }
                        Yii::$app->session->setFlash('success', 'Applicant details saved successfully.');
                        return $this->redirect(['view', 'applicant_user_id' => $applicant_user_id]);
                    } else {
                        $activeRenderStep = $currentProcessingStep;
                        $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
                        $messageForUser = $finalSaveResult['message'] ?? $stepRenderData['message'] ?? 'Failed to save details.';
                        if ($request->isAjax) return ['success' => false, 'errors' => $finalSaveResult['errors'] ?? [], 'message' => $messageForUser];
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
                    if ($currentProcessingStep === self::STEP_PERSONAL_DETAILS) {
                        // Errors could be from $model (AppApplicantUser) or $appApplicantModel (AppApplicant)
                        $errors = array_merge($model->getErrors(), $appApplicantModel->getErrors());
                    } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                        // This step is now largely a pass-through or confirmation.
                        // Errors here would be if the step itself had validation, which it currently doesn't.
                        // If $appApplicantModel was specifically loaded and validated here:
                        // if (isset($appApplicantModel) && $appApplicantModel->load($postData)) $errors = $appApplicantModel->getErrors();
                        // else $errors = ['applicant_specifics' => 'Could not load data.'];
                         $errors = []; // No specific errors for this step currently
                    } elseif ($currentProcessingStep === self::STEP_WORK_EXPERIENCE) {
                        $tempWorkExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);
                        $tempWorkExpModel->load($postData);
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
            // $appApplicantModel does not have specific scenarios mentioned for steps in its model,
            // but if it did, one would be set here. e.g. $appApplicantModel->scenario = AppApplicant::SCENARIO_WIZARD_STEP;
        }
        // Removed Account Settings scenario setting
        elseif ($currentStep === self::STEP_WORK_EXPERIENCE && isset($workExpModel)) {
            $workExpModel->scenario = AppApplicantWorkExp::SCENARIO_WIZARD;
        }
        // Add similar for AppApplicant if it has scenarios for its step. (e.g. for STEP_APPLICANT_SPECIFICS if it had fields)

        // Prepare personal names for JS auto-fill if available
        $personalNamesForJs = null;
        $personalDetailsSessionData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
        // Personal details data is now nested
        $userAttributes = $personalDetailsSessionData['user'] ?? [];
        if (!empty($userAttributes['first_name']) || !empty($userAttributes['surname'])) {
            $personalNamesForJs = [
                'firstName' => $userAttributes['first_name'] ?? '',
                'surname' => $userAttributes['surname'] ?? '',
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
        ]);

        // For initial page load, if the current step is work experience, fetch existing experiences
        if ($currentStep === self::STEP_WORK_EXPERIENCE && $applicant_user_id) {
            $appUser = AppApplicantUser::findOne($applicant_user_id);
            if ($appUser && $appUser->appApplicant) { // Check appApplicant relation
                // Ensure appApplicant itself has an ID, which is needed for linking work experiences
                if ($appUser->appApplicant->applicant_id) {
                    $renderParams['existingWorkExperiences'] = AppApplicantWorkExp::find()
                        ->where(['applicant_id' => $appUser->appApplicant->applicant_id])
                        ->orderBy(['year_from' => SORT_DESC])
                        ->asArray()
                        ->all();
                } else {
                     $renderParams['existingWorkExperiences'] = []; // No PK on AppApplicant yet
                }
            }
        }
        return $this->render('update-wizard', $renderParams);
    }

    protected function loadModelsForStep($step, $applicant_user_id, $session, $wizardDataKeyPrefix, $existingModel = null, $existingAppApplicantModel = null)
    {
        if ($existingModel instanceof AppApplicantUser) {
            $model = ($applicant_user_id && $existingModel->applicant_user_id != $applicant_user_id) ? $this->findModel($applicant_user_id) : $existingModel;
        } else {
            $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        }

        if ($existingAppApplicantModel instanceof AppApplicant) {
            $appApplicantModel = $existingAppApplicantModel;
        } else {
            $appApplicantModel = ($applicant_user_id && $model->appApplicant) ? $model->appApplicant : new AppApplicant();
        }

        if ($applicant_user_id && !$model->isNewRecord && !$appApplicantModel->applicant_user_id) {
            $appApplicantModel->applicant_user_id = $model->applicant_user_id;
        }

        $workExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);

        $dataForThisStep = $session->get($wizardDataKeyPrefix . 'data_step_' . $step, []);
        if (!empty($dataForThisStep)) {
            if ($step === self::STEP_PERSONAL_DETAILS) {
                // Data is now nested: ['user' => {...}, 'applicant' => {...}]
                if (isset($dataForThisStep['user'])) {
                    $model->setAttributes($dataForThisStep['user'], false);
                }
                if (isset($dataForThisStep['applicant'])) {
                    $appApplicantModel->setAttributes($dataForThisStep['applicant'], false);
                }
            } elseif ($step === self::STEP_APPLICANT_SPECIFICS) {
                // This step is now empty of fields by default, but if it had data:
                // $appApplicantModel->setAttributes($dataForThisStep, false); // Or whatever model it uses
            }
            // Removed STEP_ACCOUNT_SETTINGS handling
            elseif ($step === self::STEP_WORK_EXPERIENCE) {
                $workExpModel->setAttributes($dataForThisStep, false);
            }
        }
        return [$model, $appApplicantModel, $workExpModel];
    }

    protected function performFinalSave($applicant_user_id, $session, $wizardDataKeyPrefix)
    {
        if (empty($applicant_user_id)) {
            return ['success' => false, 'message' => 'Applicant User ID is missing.', 'errors' => []];
        }

        $finalModel = $this->findModel($applicant_user_id); // AppApplicantUser
        $finalAppApplicantModel = $finalModel->appApplicant ?? new AppApplicant();
        if ($finalAppApplicantModel->isNewRecord) {
            $finalAppApplicantModel->applicant_user_id = $applicant_user_id;
        }

        // Load data from session for each step
        $personalDetailsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
        // $applicantSpecificsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_APPLICANT_SPECIFICS, []); // This step is now empty of fields
        $workExpData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_WORK_EXPERIENCE, []);
        // Account settings data is removed

        // Set attributes for AppApplicantUser from personal details step
        if (isset($personalDetailsData['user'])) {
            $finalModel->setAttributes($personalDetailsData['user'], false);
        }
        $finalModel->scenario = AppApplicantUser::SCENARIO_DEFAULT; // Full validation

        // Set attributes for AppApplicant from personal details step
        if (isset($personalDetailsData['applicant'])) {
            $finalAppApplicantModel->setAttributes($personalDetailsData['applicant'], false);
        }
        // $finalAppApplicantModel->scenario = AppApplicant::SCENARIO_DEFAULT; // If it has scenarios

        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($finalModel->save()) {
                if ($finalAppApplicantModel->save()) {
                    // Handle AppApplicantWorkExp - this logic assumes multiple entries are not saved from one step,
                    // but rather one at a time if "save on next" was used, or one final entry if submitted at end.
                    // If workExpData from session represents a single record to be saved/updated:
                    if (!empty($workExpData)) {
                         // If work experiences were saved one-by-one and session cleared, this $workExpData might be empty.
                         // If it's meant to save a pending one from session:
                        $finalWorkExpModel = new AppApplicantWorkExp(['scenario' => AppApplicantWorkExp::SCENARIO_WIZARD]);
                        $finalWorkExpModel->setAttributes($workExpData, false);
                        $finalWorkExpModel->applicant_id = $finalAppApplicantModel->applicant_id;

                        if (!$finalWorkExpModel->save()) {
                            $transaction->rollBack();
                            Yii::error($finalWorkExpModel->errors);
                            return ['success' => false, 'message' => 'Error saving work experience: ' . Html::errorSummary($finalWorkExpModel), 'errors' => $finalWorkExpModel->getErrors()];
                        }
                    }

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
            Yii::error("Exception during final save for applicant_user_id {$applicant_user_id}: " . $e->getMessage() . "\nTrace: " . $e->getTraceAsString());
            return ['success' => false, 'message' => 'An unexpected error occurred: ' . $e->getMessage(), 'errors' => []];
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
