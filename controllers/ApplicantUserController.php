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
use yii\helpers\Html;
use yii\web\UploadedFile;
use yii\helpers\FileHelper;
use app\components\FormWizard\WizardController as GenericWizardController; // Import the generic wizard

class ApplicantUserController extends Controller
{
    // Old step constants can be removed or kept for reference if step keys are the same
    // const STEP_PERSONAL_DETAILS = 'personal-details';
    // const STEP_APPLICANT_SPECIFICS = 'applicant-specifics';
    // const STEP_ACCOUNT_SETTINGS = 'account-settings';

    // private $_steps = [
    //     self::STEP_PERSONAL_DETAILS,
    //     self::STEP_APPLICANT_SPECIFICS,
    //     self::STEP_ACCOUNT_SETTINGS,
    // ];

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
                        'update-wizard' => ['GET', 'POST'], // Allow POST for wizard submissions
                    ],
                ],
            ]
        );
    }

    private function getApplicantWizardConfig($applicant_user_id)
    {
        // $this context is ApplicantUserController
        $controllerInstance = $this;

        return [
            'wizardId' => 'applicantUpdateWizard',
            'sessionKeyPrefix' => 'applicant_wizard_',
            'enableSessionStorage' => true,
            'ajaxEnabled' => true,
            'viewLayout' => '@app/views/applicant-user/update-wizard-layout.php', // New layout for generic wizard
            'steps' => [
                'personal-details' => [
                    'title' => 'Personal Details',
                    'view' => '@app/views/applicant-user/personal-details', // Keep existing partial views
                    'modelClass' => AppApplicantUser::class,
                    'scenario' => AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS,
                ],
                'applicant-specifics' => [
                    'title' => 'Applicant Specifics',
                    'view' => '@app/views/applicant-user/applicant-specifics',
                    'modelClass' => AppApplicant::class,
                    // No specific scenario for AppApplicant in old code for this step
                ],
                'account-settings' => [
                    'title' => 'Account Settings',
                    'view' => '@app/views/applicant-user/account-settings',
                    'modelClass' => AppApplicantUser::class,
                    'scenario' => AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS,
                    'onBeforeProcess' => function($stepConfig, &$postData, $wizardData) use ($controllerInstance, $applicant_user_id) {
                        // Handle file upload for profile image
                        $model = $applicant_user_id ? $controllerInstance->findModel($applicant_user_id) : new AppApplicantUser();
                        $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
                        $model->profile_image_file = UploadedFile::getInstance($model, 'profile_image_file');

                        // Preserve old image if no new one is uploaded
                        if (isset($postData[$model->formName()]) && !$model->profile_image_file) {
                             $existingModel = $applicant_user_id ? $controllerInstance->findModel($applicant_user_id) : null;
                             if ($existingModel && !empty($wizardData['account-settings']['profile_image'])) { // from session
                                $postData[$model->formName()]['profile_image'] = $wizardData['account-settings']['profile_image'];
                             } elseif ($existingModel) {
                                $postData[$model->formName()]['profile_image'] = $existingModel->profile_image;
                             }
                        }
                        return true;
                    },
                ],
            ],
            'callbacks' => [
                'loadStepData' => function($stepKey, $wizardData) use ($controllerInstance, $applicant_user_id) {
                    // Load existing data for a step, e.g., from database or session
                    $session = Yii::$app->session;
                    $appUser = null;
                    $appApplicant = null;

                    if ($applicant_user_id) {
                        $appUser = $controllerInstance->findModel($applicant_user_id);
                        $appApplicant = $appUser->appApplicant ?? new AppApplicant();
                    } else {
                        $appUser = new AppApplicantUser();
                        $appApplicant = new AppApplicant();
                    }

                    // Prioritize data from wizard's session storage if available
                    if (isset($wizardData[$stepKey])) {
                        return $wizardData[$stepKey];
                    }

                    // Otherwise, load from DB models
                    if ($stepKey === 'personal-details' && $appUser) {
                        return $appUser->getAttributes();
                    } elseif ($stepKey === 'applicant-specifics' && $appApplicant) {
                        return $appApplicant->getAttributes();
                    } elseif ($stepKey === 'account-settings' && $appUser) {
                        // Exclude password fields from pre-populating form unless specifically handled
                        return $appUser->getAttributes(null, ['password_hash', 'password', 'change_pass']);
                    }
                    return [];
                },
                'validateStepData' => function($stepKey, $stepData, $stepConfig, &$errors) use ($controllerInstance, $applicant_user_id) {
                    $modelClass = $stepConfig['modelClass'];
                    $model = null;
                    $isNewUserRecord = false;

                    if ($stepKey === 'personal-details') {
                        $model = $applicant_user_id ? $controllerInstance->findModel($applicant_user_id) : new AppApplicantUser();
                        $isNewUserRecord = $model->isNewRecord;
                    } elseif ($stepKey === 'applicant-specifics') {
                        // AppApplicant depends on AppApplicantUser existing
                        if (!$applicant_user_id && !isset($wizardData['personal-details']['applicant_user_id'])) {
                             Yii::error("Cannot validate applicant-specifics without applicant_user_id from previous step.");
                             $errors['general'] = "Personal details must be saved first.";
                             return false;
                        }
                        $currentUserId = $applicant_user_id ?: ($wizardData['personal-details']['applicant_user_id'] ?? null);
                        if(!$currentUserId) {
                            $errors['general'] = "User ID missing for applicant specifics.";
                            return false;
                        }
                        $userModel = $controllerInstance->findModel($currentUserId);
                        $model = $userModel->appApplicant ?? new AppApplicant(['applicant_user_id' => $currentUserId]);

                    } elseif ($stepKey === 'account-settings') {
                         if (!$applicant_user_id && !isset($wizardData['personal-details']['applicant_user_id'])) {
                             $errors['general'] = "Personal details must be saved first.";
                             return false;
                         }
                        $currentUserId = $applicant_user_id ?: ($wizardData['personal-details']['applicant_user_id'] ?? null);
                        $model = $controllerInstance->findModel($currentUserId);
                    }

                    if (!$model) {
                        $errors['general'] = "Could not load model for validation.";
                        return false;
                    }

                    if (!empty($stepConfig['scenario'])) {
                        $model->scenario = $stepConfig['scenario'];
                    }

                    $model->load($stepData, ''); // Load without form name, as $stepData is attributes array

                    // Special handling for profile image in account-settings
                    if ($stepKey === 'account-settings') {
                        // profile_image_file is already handled by onBeforeProcess
                        // If profile_image_file was set, it will be validated by model rules
                        // If not, ensure existing profile_image is preserved if it's in $stepData
                         if (isset($stepData['profile_image_file']) && $stepData['profile_image_file'] instanceof UploadedFile) {
                            $model->profile_image_file = $stepData['profile_image_file'];
                         } elseif(isset($stepData['profile_image'])) {
                             $model->profile_image = $stepData['profile_image'];
                         }
                    }


                    if ($model->validate()) {
                        // For personal-details, if it's a new user, save it now to get applicant_user_id
                        if ($stepKey === 'personal-details' && $isNewUserRecord) {
                            if ($model->save(false)) { // Save without re-validating
                                // This is tricky: wizardData isn't directly available here to update applicant_user_id for subsequent steps
                                // The generic wizard should perhaps allow returning context/ID on successful step save.
                                // For now, rely on onWizardComplete to use the final ID.
                                // Or, the generic wizard could pass $wizardData by reference to validateStepData.
                                Yii::$app->session->set('applicant_wizard_just_created_id', $model->applicant_user_id);
                            } else {
                                $errors = $model->getErrors();
                                Yii::error("Failed to save new AppApplicantUser in wizard step: " . print_r($errors, true));
                                return false;
                            }
                        }
                        // If account settings and image uploaded, save image here (or in onWizardComplete)
                        if($stepKey === 'account-settings' && $model->profile_image_file) {
                             $uploadPath = Yii::getAlias('@webroot/img/profile/');
                             FileHelper::createDirectory($uploadPath);
                             $uniqueFilename = Yii::$app->security->generateRandomString() . '.' . $model->profile_image_file->extension;
                             $filePath = $uploadPath . $uniqueFilename;
                             if ($model->profile_image_file->saveAs($filePath)) {
                                 // Remove old image if exists and different
                                 if ($model->profile_image && $model->profile_image !== $uniqueFilename && file_exists($uploadPath . $model->profile_image)) {
                                     @unlink($uploadPath . $model->profile_image);
                                 }
                                 $model->profile_image = $uniqueFilename; // This will be saved to wizardData
                             } else {
                                 $errors['profile_image_file'] = 'Could not save uploaded image.';
                                 return false;
                             }
                        }
                        return true;
                    } else {
                        $errors = $model->getErrors();
                        return false;
                    }
                },
                'saveStepData' => function($stepKey, $validatedStepData, &$wizardDataGlobal) use ($applicant_user_id) {
                    // This callback in generic wizard stores $validatedStepData into $wizardDataGlobal[$stepKey]
                    // We might need to update the applicant_user_id in the global wizard data if it was just created.
                    if ($stepKey === 'personal-details' && Yii::$app->session->has('applicant_wizard_just_created_id')) {
                        $newlyCreatedId = Yii::$app->session->get('applicant_wizard_just_created_id');
                        // This is a bit of a hack. Ideally, validateStepData or the main controller
                        // should update the wizard's context if an ID is generated.
                        $wizardDataGlobal['applicant_user_id_holder'] = $newlyCreatedId; // Store it somewhere generic wizard can see
                        Yii::$app->session->remove('applicant_wizard_just_created_id');
                    }
                    // If account settings, ensure profile_image (filename) is in validatedStepData
                    if ($stepKey === 'account-settings' && isset($validatedStepData['profile_image_file'])) {
                        // The actual file instance shouldn't be in session. The filename is already set on model.
                        unset($validatedStepData['profile_image_file']);
                    }

                    $wizardDataGlobal[$stepKey] = $validatedStepData; // Default behavior of generic wizard
                    return true;
                }
            ],
            'onWizardComplete' => function($allWizardData) use ($controllerInstance, $applicant_user_id) {
                $current_applicant_user_id = $applicant_user_id ?: ($allWizardData['applicant_user_id_holder'] ?? null);

                if (empty($current_applicant_user_id)) {
                    // This might happen if first step (personal-details) didn't save or its ID wasn't propagated.
                     // Attempt to retrieve from first step data if not in holder (e.g. if user re-visits wizard for existing record)
                    if(isset($allWizardData['personal-details']['applicant_user_id'])) {
                        $current_applicant_user_id = $allWizardData['personal-details']['applicant_user_id'];
                    } else {
                        Yii::error("Wizard completion: Applicant User ID is missing.", __METHOD__);
                        return ['success' => false, 'message' => 'Critical error: Applicant User ID is missing. Cannot save.', 'errors' => []];
                    }
                }

                $finalModelUser = $controllerInstance->findModel($current_applicant_user_id);
                $finalModelApplicant = $finalModelUser->appApplicant ?? new AppApplicant();
                $finalModelApplicant->applicant_user_id = $current_applicant_user_id; // Ensure it's set

                // Load data from relevant steps
                // Personal details might have been saved partially if it was a new record.
                // Or updated if existing.
                if (isset($allWizardData['personal-details'])) {
                    $finalModelUser->setAttributes($allWizardData['personal-details'], false);
                }
                if (isset($allWizardData['applicant-specifics'])) {
                    $finalModelApplicant->setAttributes($allWizardData['applicant-specifics'], false);
                }
                if (isset($allWizardData['account-settings'])) {
                    // Exclude password fields unless they are explicitly part of this step's data
                    // profile_image filename should be in $allWizardData['account-settings']['profile_image']
                    $accountAttrs = array_diff_key($allWizardData['account-settings'], array_flip(['password', 'change_pass', 'profile_image_file']));
                    $finalModelUser->setAttributes($accountAttrs, false);
                }

                $finalModelUser->scenario = AppApplicantUser::SCENARIO_DEFAULT; // Reset scenario for full validation if needed

                $transaction = Yii::$app->db->beginTransaction();
                try {
                    // It's possible personal details for a new user were already saved.
                    // Save user model again for any updates from other steps (e.g. account settings)
                    if ($finalModelUser->save()) {
                        if ($finalModelApplicant->isNewRecord && empty($finalModelApplicant->getDirtyAttributes())) {
                            // If it's new and has no data, don't try to save it.
                            $transaction->commit();
                             return ['success' => true, 'message' => 'Applicant details saved successfully!', 'redirectUrl' => \yii\helpers\Url::to(['view', 'applicant_user_id' => $finalModelUser->applicant_user_id])];
                        }
                        if ($finalModelApplicant->save()) {
                            $transaction->commit();
                            return ['success' => true, 'message' => 'Applicant details saved successfully!', 'redirectUrl' => \yii\helpers\Url::to(['view', 'applicant_user_id' => $finalModelUser->applicant_user_id])];
                        } else {
                            $transaction->rollBack();
                            Yii::error("Wizard Final Save Error (AppApplicant): " . print_r($finalModelApplicant->errors, true), __METHOD__);
                            return ['success' => false, 'message' => 'Error saving applicant specifics: ' . Html::errorSummary($finalModelApplicant), 'errors' => $finalModelApplicant->getErrors()];
                        }
                    } else {
                        $transaction->rollBack();
                        Yii::error("Wizard Final Save Error (AppApplicantUser): " . print_r($finalModelUser->errors, true), __METHOD__);
                        return ['success' => false, 'message' => 'Error saving applicant user details: ' . Html::errorSummary($finalModelUser), 'errors' => $finalModelUser->getErrors()];
                    }
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    Yii::error("Wizard Final Save Exception: " . $e->getMessage(), __METHOD__);
                    return ['success' => false, 'message' => 'An unexpected error occurred during final save: ' . $e->getMessage(), 'errors' => []];
                }
            },
            'onWizardCancel' => function($allWizardData) {
                 return ['success' => true, 'message' => 'Wizard cancelled.', 'redirectUrl' => \yii\helpers\Url::to(['index'])];
            },
            'buttons' => [
                 'cancel' => ['label' => 'Cancel', 'options' => ['class' => 'btn btn-warning ms-2'], 'url' => ['applicant-user/index']],
            ]
        ];
    }

    public function actionUpdateWizard($applicant_user_id = null, $requested_step_key = null)
    {
        $request = Yii::$app->request;

        // If applicant_user_id is not in URL, try to get from session (if wizard was started)
        // The generic wizard itself handles sessioning of its internal current step and data.
        // We only need applicant_user_id to load the correct models.
        if ($applicant_user_id === null) {
            // Check if wizard session has an applicant_user_id from a previous step (e.g. after personal details save)
            // This logic might need to be more robust or rely on the wizard's own data storage.
            $sessionWizardData = Yii::$app->session->get('applicant_wizard_applicantUpdateWizard_data', []);
            if (isset($sessionWizardData['applicant_user_id_holder'])) {
                $applicant_user_id = $sessionWizardData['applicant_user_id_holder'];
            } elseif (isset($sessionWizardData['personal-details']['applicant_user_id'])) {
                 $applicant_user_id = $sessionWizardData['personal-details']['applicant_user_id'];
            }
        }


        $wizardConfig = $this->getApplicantWizardConfig($applicant_user_id);
        $genericWizard = new GenericWizardController($wizardConfig);

        // The generic wizard's handleRequest will manage current step based on POST or its session.
        // $requested_step_key from URL is passed to it.
        $response = $genericWizard->handleRequest($requested_step_key ?: $request->get('requested_step_key'));

        if ($request->isAjax) {
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            return $response;
        } else {
            if (isset($response['redirectTo'])) { // Non-AJAX redirect
                return $this->redirect($response['redirectTo']);
            }
            // Non-AJAX: $response contains 'fullPageHtml' or just 'html' for error
            if (isset($response['fullPageHtml'])) {
                return $response['fullPageHtml']; // This is already rendered layout + content
            } else {
                // Fallback or error display
                return $this->renderContent($response['html'] ?? 'Error in wizard processing.');
            }
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
