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
        $wizardDataKeyPrefix = 'applicant_wizard_';

        // Initialize applicant_user_id: from param, then session, then null
        if ($applicant_user_id === null) {
            $applicant_user_id = $session->get($wizardDataKeyPrefix . 'applicant_user_id');
        } else {
            // If applicant_user_id is passed in URL, it might be start of new wizard or specific record
            // If session exists and ID differs, could be starting new wizard for different user, clear old session
            if ($session->has($wizardDataKeyPrefix . 'applicant_user_id') && $session->get($wizardDataKeyPrefix . 'applicant_user_id') != $applicant_user_id) {
                foreach ($this->_steps as $s) {
                    $session->remove($wizardDataKeyPrefix . 'data_step_' . $s);
                }
            }
            $session->set($wizardDataKeyPrefix . 'applicant_user_id', $applicant_user_id);
        }

        // Determine current step
        $requestedStep = $currentStep; // Keep track of initially requested step if any
        if ($currentStep === null) {
            $currentStep = $session->get($wizardDataKeyPrefix . 'current_step', self::STEP_PERSONAL_DETAILS);
        }
        if (!in_array($currentStep, $this->_steps)) {
            $currentStep = self::STEP_PERSONAL_DETAILS; // Default to first step if invalid
        }
        // $session->set($wizardDataKeyPrefix . 'current_step', $currentStep); // Set later after processing POST

        // Load or instantiate models
        $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        $appApplicantModel = ($applicant_user_id && $model->appApplicant) ? $model->appApplicant : new AppApplicant();

        // Ensure AppApplicant model has applicant_user_id if model (AppApplicantUser) exists and has an ID
        if ($applicant_user_id && !$appApplicantModel->applicant_user_id) {
            $appApplicantModel->applicant_user_id = $applicant_user_id;
        }

        $stepRenderData = ['message' => null];
        $isValid = false; // To track if current step processing was valid

        // Set scenario for the model based on the current step (even for GET, for client-side validation hints)
        // This should be based on the $currentStep that will be rendered.
        $activeRenderStep = $currentStep; // Initially same, might change after POST processing

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();
            $currentProcessingStep = $session->get($wizardDataKeyPrefix . 'current_step', $currentStep); // Step being submitted
            $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $currentProcessingStep;


            if (isset($postData['wizard_cancel'])) {
                foreach ($this->_steps as $s) { $session->remove($wizardDataKeyPrefix . 'data_step_' . $s); }
                $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                $session->remove($wizardDataKeyPrefix . 'current_step');
                Yii::$app->session->setFlash('info', 'Wizard cancelled.');
                return $this->redirect(['index']);
            }

            $currentStepIndex = array_search($currentProcessingStep, $this->_steps);

            if (isset($postData['wizard_previous'])) {
                if ($currentStepIndex > 0) {
                    $activeRenderStep = $this->_steps[$currentStepIndex - 1];
                }
                // No data saving on previous
            } elseif (isset($postData['wizard_next']) || isset($postData['wizard_save'])) {
                // Load and validate current step's model
                if ($currentProcessingStep === self::STEP_PERSONAL_DETAILS) {
                    $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
                    if ($model->load($postData) && $model->validate()) {
                        $session->set($stepSessionKey, $model->getAttributes());
                        $isValid = true;
                        if ($model->isNewRecord) {
                            if ($model->save(false)) {
                                $applicant_user_id = $model->applicant_user_id;
                                $session->set($wizardDataKeyPrefix . 'applicant_user_id', $applicant_user_id);
                                $appApplicantModel->applicant_user_id = $applicant_user_id; // Link AppApplicant
                            } else { $isValid = false; $stepRenderData['message'] = 'Failed to save personal details.'; Yii::error($model->errors); }
                        } else {
                             if (!$model->save(false)) { $isValid = false; $stepRenderData['message'] = 'Failed to update personal details.'; Yii::error($model->errors); }
                        }
                    } else { $stepRenderData['message'] = 'Please correct errors in Personal Details.'; }
                } elseif ($currentProcessingStep === self::STEP_APPLICANT_SPECIFICS) {
                    // $appApplicantModel->scenario = ... ; // if exists
                    if ($appApplicantModel->load($postData) && $appApplicantModel->validate()) {
                        $session->set($stepSessionKey, $appApplicantModel->getAttributes());
                        $isValid = true;
                        // No immediate save for AppApplicantModel here, saved at the end or if explicitly designed
                    } else { $stepRenderData['message'] = 'Please correct errors in Applicant Specifics.'; }
                } elseif ($currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) {
                    $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
                    if ($model->load($postData) && $model->validate()) {
                        $session->set($stepSessionKey, $model->getAttributes(['username', 'password', 'profile_image', 'change_pass']));
                        $isValid = true;
                         // No immediate save for account settings here, saved at the end
                    } else { $stepRenderData['message'] = 'Please correct errors in Account Settings.';}
                }

                if ($isValid) {
                    if (isset($postData['wizard_next'])) {
                        if ($currentStepIndex < count($this->_steps) - 1) {
                            $activeRenderStep = $this->_steps[$currentStepIndex + 1];
                        } else { $activeRenderStep = $currentProcessingStep; /* Stay on last step */ }
                    } elseif (isset($postData['wizard_save'])) {
                        if ($currentProcessingStep === self::STEP_ACCOUNT_SETTINGS) { // Final save on last step
                            $personalDetailsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
                            $applicantSpecificsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_APPLICANT_SPECIFICS, []);
                            $accountSettingsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_ACCOUNT_SETTINGS, []);

                            if (empty($applicant_user_id)) {
                                 Yii::$app->session->setFlash('error', 'Applicant User ID is missing. Cannot save.');
                                 $activeRenderStep = self::STEP_PERSONAL_DETAILS; // redirect to first step
                            } else {
                                $finalModel = $this->findModel($applicant_user_id);
                                $finalAppApplicantModel = $finalModel->getAppApplicant()->one() ?? new AppApplicant();
                                $finalAppApplicantModel->applicant_user_id = $applicant_user_id;

                                $finalModel->setAttributes($personalDetailsData, false); // Already saved if new/changed
                                $finalModel->setAttributes($accountSettingsData, false);
                                $finalAppApplicantModel->setAttributes($applicantSpecificsData, false);

                                $finalModel->scenario = AppApplicantUser::SCENARIO_DEFAULT;

                                $transaction = Yii::$app->db->beginTransaction();
                                try {
                                    if ($finalModel->save()) {
                                        if ($finalAppApplicantModel->save()) {
                                            $transaction->commit();
                                            Yii::$app->session->setFlash('success', 'Applicant details saved successfully.');
                                            foreach ($this->_steps as $s) { $session->remove($wizardDataKeyPrefix . 'data_step_' . $s); }
                                            $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                                            $session->remove($wizardDataKeyPrefix . 'current_step');
                                            return $this->redirect(['view', 'applicant_user_id' => $finalModel->applicant_user_id]);
                                        } else {
                                            $transaction->rollBack();
                                            $stepRenderData['message'] = 'Error saving applicant specifics: ' . Html::errorSummary($finalAppApplicantModel); Yii::error($finalAppApplicantModel->errors);
                                        }
                                    } else {
                                        $transaction->rollBack();
                                        $stepRenderData['message'] = 'Error saving applicant user details: ' . Html::errorSummary($finalModel); Yii::error($finalModel->errors);
                                    }
                                } catch (\Exception $e) {
                                    $transaction->rollBack();
                                    $stepRenderData['message'] = 'An error occurred: ' . $e->getMessage(); Yii::error($e->getMessage());
                                }
                                $activeRenderStep = $currentProcessingStep; // Stay on current step if save failed
                            }
                        }
                    }
                } else { // Not $isValid
                    $activeRenderStep = $currentProcessingStep; // Stay on current step if validation failed
                }
            }
            $session->set($wizardDataKeyPrefix . 'current_step', $activeRenderStep);
            $currentStep = $activeRenderStep; // Update currentStep for rendering
        } else { // GET Request
             $session->set($wizardDataKeyPrefix . 'current_step', $currentStep); // Set current step for GET
        }


        // Load data from session for the step that will be rendered
        // This ensures that if we navigate (Next/Previous) or stay due to error, correct data is shown
        $renderStepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $currentStep;
        $renderStepDataFromSession = $session->get($renderStepSessionKey, []);

        if ($currentStep === self::STEP_PERSONAL_DETAILS || $currentStep === self::STEP_ACCOUNT_SETTINGS) {
            // For personal details and account settings, $model is primary.
            // If coming from POST and it was valid and we moved step, $model might be "empty" for the new step.
            // If staying on step due to error, $model already has POSTed data.
            // If GET, load from session.
            if (!Yii::$app->request->isPost || (Yii::$app->request->isPost && $isValid && $currentProcessingStep !== $currentStep) || (Yii::$app->request->isPost && !$isValid) ) {
                 if(!empty($renderStepDataFromSession)) $model->setAttributes($renderStepDataFromSession, false);
            }
        } elseif ($currentStep === self::STEP_APPLICANT_SPECIFICS) {
            // Similarly for $appApplicantModel
             if (!Yii::$app->request->isPost || (Yii::$app->request->isPost && $isValid && $currentProcessingStep !== $currentStep) || (Yii::$app->request->isPost && !$isValid) ) {
                if(!empty($renderStepDataFromSession)) $appApplicantModel->setAttributes($renderStepDataFromSession, false);
             }
        }

        // Set scenario for the actual step being rendered
        if ($currentStep === self::STEP_PERSONAL_DETAILS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
        } elseif ($currentStep === self::STEP_ACCOUNT_SETTINGS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
        } // Add for $appApplicantModel if needed

        return $this->render('update-wizard', [
            'currentStep' => $currentStep,
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
            'stepData' => $stepRenderData,
            'steps' => $this->_steps,
        ]);
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
