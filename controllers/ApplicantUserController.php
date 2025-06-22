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
        if ($currentStep === null) {
            $currentStep = $session->get($wizardDataKeyPrefix . 'current_step', self::STEP_PERSONAL_DETAILS);
        }
        if (!in_array($currentStep, $this->_steps)) {
            $currentStep = self::STEP_PERSONAL_DETAILS; // Default to first step if invalid
        }
        $session->set($wizardDataKeyPrefix . 'current_step', $currentStep);

        // Load or instantiate models
        $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        $appApplicantModel = ($applicant_user_id && !$model->isNewRecord) ? ($model->getAppApplicant()->one() ?? new AppApplicant()) : new AppApplicant();

        // Ensure AppApplicant model has applicant_user_id if model exists
        if ($applicant_user_id && !$appApplicantModel->applicant_user_id) {
            $appApplicantModel->applicant_user_id = $applicant_user_id;
        }

        // Load data from session for the current step
        $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $currentStep;
        $stepDataFromSession = $session->get($stepSessionKey, []);

        // Set scenario for the model based on the current step FOR GET requests
        // This helps ActiveForm generate correct client-side validation
        if ($currentStep === self::STEP_PERSONAL_DETAILS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
        } elseif ($currentStep === self::STEP_ACCOUNT_SETTINGS) {
            $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
        }
        // AppApplicant model might have its own scenario logic if needed, or uses default.

        if (!Yii::$app->request->isPost) { // For GET requests, populate models from session AFTER setting scenario
            if ($currentStep === self::STEP_PERSONAL_DETAILS || $currentStep === self::STEP_ACCOUNT_SETTINGS) {
                if (!empty($stepDataFromSession)) {
                    $model->setAttributes($stepDataFromSession, false);
                }
            } elseif ($currentStep === self::STEP_APPLICANT_SPECIFICS) {
                if (!empty($stepDataFromSession)) {
                    $appApplicantModel->setAttributes($stepDataFromSession, false);
                }
            }
        }

        $stepRenderData = ['message' => null]; // For messages like validation errors

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            if (isset($postData['wizard_cancel'])) {
                foreach ($this->_steps as $s) {
                    $session->remove($wizardDataKeyPrefix . 'data_step_' . $s);
                }
                $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                $session->remove($wizardDataKeyPrefix . 'current_step');
                Yii::$app->session->setFlash('info', 'Wizard cancelled.');
                return $this->redirect(['index']);
            }

            $currentStepIndex = array_search($currentStep, $this->_steps);

            if (isset($postData['wizard_previous'])) {
                if ($currentStepIndex > 0) {
                    $currentStep = $this->_steps[$currentStepIndex - 1];
                    $session->set($wizardDataKeyPrefix . 'current_step', $currentStep);
                    // Reload data for the new current step (previous step)
                    $stepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $currentStep;
                    $stepDataFromSession = $session->get($stepSessionKey, []);
                    if ($currentStep === self::STEP_PERSONAL_DETAILS || $currentStep === self::STEP_ACCOUNT_SETTINGS) {
                        $model->setAttributes($stepDataFromSession, false);
                    } elseif ($currentStep === self::STEP_APPLICANT_SPECIFICS) {
                        $appApplicantModel->setAttributes($stepDataFromSession, false);
                    }
                }
                // No return here, will fall through to render the (previous) step
            } elseif (isset($postData['wizard_next']) || isset($postData['wizard_save'])) {
                $isValid = false;
                if ($currentStep === self::STEP_PERSONAL_DETAILS) {
                    $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
                    if ($model->load($postData) && $model->validate()) {
                        $session->set($stepSessionKey, $model->getAttributes());
                        $isValid = true;
                        // Save personal details immediately if new, to get an ID
                        if ($model->isNewRecord) {
                            if ($model->save(false)) { // Save without re-validating
                                $applicant_user_id = $model->applicant_user_id;
                                $session->set($wizardDataKeyPrefix . 'applicant_user_id', $applicant_user_id);
                                // Ensure AppApplicant model also gets this ID for subsequent steps
                                $appApplicantModel->applicant_user_id = $applicant_user_id;
                            } else {
                                $isValid = false;
                                $stepRenderData['message'] = 'Failed to save personal details.';
                                Yii::error($model->errors);
                            }
                        } else { // If updating, just save
                            if (!$model->save(false)) {
                                $isValid = false;
                                $stepRenderData['message'] = 'Failed to update personal details.';
                                Yii::error($model->errors);
                            }
                        }
                    } else {
                        $stepRenderData['message'] = 'Please correct errors in Personal Details.';
                    }
                } elseif ($currentStep === self::STEP_APPLICANT_SPECIFICS) {
                    // Ensure applicant_user_id is set on appApplicantModel before loading
                    if ($applicant_user_id && !$appApplicantModel->applicant_user_id) {
                        $appApplicantModel->applicant_user_id = $applicant_user_id;
                    }
                    if ($appApplicantModel->load($postData) && $appApplicantModel->validate()) {
                        $session->set($stepSessionKey, $appApplicantModel->getAttributes());
                        $isValid = true;
                    } else {
                        $stepRenderData['message'] = 'Please correct errors in Applicant Specifics.';
                    }
                } elseif ($currentStep === self::STEP_ACCOUNT_SETTINGS) {
                    $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
                    if ($model->load($postData) && $model->validate()) {
                        $session->set($stepSessionKey, $model->getAttributes(['username', 'password', 'profile_image', 'change_pass']));
                        $isValid = true;
                    } else {
                        $stepRenderData['message'] = 'Please correct errors in Account Settings.';
                    }
                }

                if ($isValid) {
                    if (isset($postData['wizard_next'])) {
                        if ($currentStepIndex < count($this->_steps) - 1) {
                            $currentStep = $this->_steps[$currentStepIndex + 1];
                            $session->set($wizardDataKeyPrefix . 'current_step', $currentStep);
                            // Load data for the new current step (next step) if it exists
                            $nextStepSessionKey = $wizardDataKeyPrefix . 'data_step_' . $currentStep;
                            $nextStepDataFromSession = $session->get($nextStepSessionKey, []);
                            if ($currentStep === self::STEP_PERSONAL_DETAILS || $currentStep === self::STEP_ACCOUNT_SETTINGS) {
                                // We need to ensure $model is the correct one for this step
                                // If $applicant_user_id was just created, $model should already be the correct one.
                                // If we are navigating back and forth, $model is the main user model.
                                $model->setAttributes($nextStepDataFromSession, false);
                            } elseif ($currentStep === self::STEP_APPLICANT_SPECIFICS) {
                                $appApplicantModel->setAttributes($nextStepDataFromSession, false);
                            }
                        }
                    } elseif (isset($postData['wizard_save'])) { // Final Save
                        if ($currentStep === self::STEP_ACCOUNT_SETTINGS) { // Ensure it's the last step
                            // Retrieve all data from session
                            $personalDetailsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_PERSONAL_DETAILS, []);
                            $applicantSpecificsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_APPLICANT_SPECIFICS, []);
                            $accountSettingsData = $session->get($wizardDataKeyPrefix . 'data_step_' . self::STEP_ACCOUNT_SETTINGS, []);

                            if (empty($applicant_user_id)) {
                                Yii::$app->session->setFlash('error', 'Applicant User ID is missing. Cannot save.');
                                return $this->redirect(['update-wizard', 'currentStep' => self::STEP_PERSONAL_DETAILS]);
                            }

                            $finalModel = $this->findModel($applicant_user_id);
                            $finalAppApplicantModel = $finalModel->getAppApplicant()->one() ?? new AppApplicant();
                            $finalAppApplicantModel->applicant_user_id = $applicant_user_id; // Ensure FK is set

                            // Set attributes carefully, respecting scenarios if necessary
                            $finalModel->setAttributes($personalDetailsData, false); // Already saved or updated
                            $finalModel->setAttributes($accountSettingsData, false);
                            $finalAppApplicantModel->setAttributes($applicantSpecificsData, false);

                            $transaction = Yii::$app->db->beginTransaction();
                            try {
                                // Personal details are saved in their step.
                                // Account settings are part of $finalModel.
                                $finalModel->scenario = AppApplicantUser::SCENARIO_DEFAULT; // Or a specific final save scenario
                                if ($finalModel->save()) {
                                    if ($finalAppApplicantModel->save()) {
                                        $transaction->commit();
                                        Yii::$app->session->setFlash('success', 'Applicant details saved successfully.');
                                        // Clear wizard session data
                                        foreach ($this->_steps as $s) {
                                            $session->remove($wizardDataKeyPrefix . 'data_step_' . $s);
                                        }
                                        $session->remove($wizardDataKeyPrefix . 'applicant_user_id');
                                        $session->remove($wizardDataKeyPrefix . 'current_step');
                                        return $this->redirect(['view', 'applicant_user_id' => $finalModel->applicant_user_id]);
                                    } else {
                                        $transaction->rollBack();
                                        $stepRenderData['message'] = 'Error saving applicant specifics: ' . print_r($finalAppApplicantModel->getErrors(), true);
                                        Yii::error($finalAppApplicantModel->getErrors());
                                    }
                                } else {
                                    $transaction->rollBack();
                                    $stepRenderData['message'] = 'Error saving applicant user details: ' . print_r($finalModel->getErrors(), true);
                                    Yii::error($finalModel->getErrors());
                                }
                            } catch (\Exception $e) {
                                $transaction->rollBack();
                                $stepRenderData['message'] = 'An error occurred: ' . $e->getMessage();
                                Yii::error($e->getMessage());
                            }
                        }
                    }
                }
                // If not valid, or if it was 'next' and now needs to render next step,
                // it will fall through to the render call below.
            }
        }

        return $this->render('update-wizard', [
            'currentStep' => $currentStep,
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
            'stepData' => $stepRenderData, // Pass messages or other step-specific render data
            'steps' => $this->_steps, // For navigation UI
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
