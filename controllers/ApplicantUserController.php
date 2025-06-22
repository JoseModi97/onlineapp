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
        // $wizardDataKeyPrefix = 'applicant_wizard_'; // Session storage per step is no longer the primary mechanism

        // Initialize or load models
        if ($applicant_user_id) {
            $model = $this->findModel($applicant_user_id);
            $appApplicantModel = $model->getAppApplicant()->one() ?? new AppApplicant();
            $appApplicantModel->applicant_user_id = $model->applicant_user_id; // Ensure FK is set
        } else {
            $model = new AppApplicantUser();
            $appApplicantModel = new AppApplicant();
            // If creating new, $applicant_user_id will be set after first model save
        }

        // Determine current step for initial rendering (e.g. if URL specifies it or returning from error)
        // The JS plugin handles actual step-to-step navigation.
        // If currentStep is not in URL, default to the first step.
        // If form submitted with errors, controller might set $currentStep to the step with the first error.
        if ($currentStep === null || !in_array($currentStep, $this->_steps)) {
            $currentStep = Yii::$app->request->get('currentStep', self::STEP_PERSONAL_DETAILS);
            if (!in_array($currentStep, $this->_steps)) {
                $currentStep = self::STEP_PERSONAL_DETAILS;
            }
        }

        $stepRenderData = ['message' => null, 'errorStepIndex' => null];

        if (Yii::$app->request->isPost) {
            $postData = Yii::$app->request->post();

            if (isset($postData['wizard_cancel'])) {
                // $session->remove($wizardDataKeyPrefix . 'applicant_user_id'); // Clean up if any session ID was stored
                // $session->remove($wizardDataKeyPrefix . 'current_step');
                Yii::$app->session->setFlash('info', 'Wizard cancelled.');
                return $this->redirect(['index']);
            }

            // Only 'wizard_save' button submits the form now
            if (isset($postData['wizard_save'])) {
                // Load data for both models from the single POST request
                $modelLoaded = $model->load($postData);
                $appApplicantModelLoaded = $appApplicantModel->load($postData);

                // Set scenarios for validation if you have them
                $model->scenario = AppApplicantUser::SCENARIO_DEFAULT; // Or a specific "wizard_save" scenario
                // $appApplicantModel->scenario = AppApplicant::SCENARIO_DEFAULT; // If applicable

                // Validate both models
                $modelValid = $model->validate();
                $appApplicantModelValid = $appApplicantModel->validate();

                if ($modelValid && $appApplicantModelValid) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model->save(false)) { // Save without re-validating
                            // If it was a new user, applicant_user_id is now available
                            // Assign it to appApplicantModel if it's also new
                            if ($model->isNewRecord || !$appApplicantModel->applicant_user_id) {
                                 // This condition might be tricky if $model was new but $appApplicantModel was somehow pre-existing without ID
                                 // More robust: if $appApplicantModel is new or its FK isn't set to the now-known $model->id
                                if ($appApplicantModel->isNewRecord || $appApplicantModel->applicant_user_id != $model->applicant_user_id) {
                                   $appApplicantModel->applicant_user_id = $model->applicant_user_id;
                                }
                            }
                            if ($appApplicantModel->save(false)) { // Save without re-validating
                                $transaction->commit();
                                Yii::$app->session->setFlash('success', 'Applicant details saved successfully.');
                                // $session->remove($wizardDataKeyPrefix . 'applicant_user_id'); // Clean up
                                // $session->remove($wizardDataKeyPrefix . 'current_step');
                                return $this->redirect(['view', 'applicant_user_id' => $model->applicant_user_id]);
                            } else {
                                $transaction->rollBack();
                                $stepRenderData['message'] = 'Error saving applicant specifics: ' . Html::errorSummary($appApplicantModel);
                                // Determine which step has the error for $appApplicantModel
                                $stepRenderData['errorStepIndex'] = array_search(self::STEP_APPLICANT_SPECIFICS, $this->_steps);
                                $currentStep = self::STEP_APPLICANT_SPECIFICS; // Ensure view knows current context
                            }
                        } else {
                            $transaction->rollBack();
                            $stepRenderData['message'] = 'Error saving applicant user details: ' . Html::errorSummary($model);
                            // Determine which step has the error for $model
                            if (count(array_intersect_key($model->getErrors(), array_flip($model->getAttributes($model->scenarios()[AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS] ?? []))))) {
                                $stepRenderData['errorStepIndex'] = array_search(self::STEP_PERSONAL_DETAILS, $this->_steps);
                                $currentStep = self::STEP_PERSONAL_DETAILS;
                            } else if (count(array_intersect_key($model->getErrors(), array_flip($model->getAttributes($model->scenarios()[AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS] ?? []))))) {
                                 $stepRenderData['errorStepIndex'] = array_search(self::STEP_ACCOUNT_SETTINGS, $this->_steps);
                                 $currentStep = self::STEP_ACCOUNT_SETTINGS;
                            } else { // Default to first step if specific step can't be determined
                                $stepRenderData['errorStepIndex'] = 0;
                                $currentStep = $this->_steps[0];
                            }
                        }
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        $stepRenderData['message'] = 'An error occurred: ' . $e->getMessage();
                        // Attempt to set a relevant step index, or default
                        $stepRenderData['errorStepIndex'] = $stepRenderData['errorStepIndex'] ?? 0;
                        $currentStep = $this->_steps[$stepRenderData['errorStepIndex']];
                    }
                } else {
                    // Validation failed, determine which step to show
                    $stepRenderData['message'] = 'Please correct the errors indicated below.';

                    // Define actual attributes for each step to improve error navigation
                    $personalDetailsAttrs = ['surname', 'first_name', 'other_name', 'email_address', 'mobile_no'];
                    $applicantSpecificsAttrs = ['gender', 'dob', 'religion', 'country_code', 'national_id', 'marital_status'];
                    $accountSettingsAttrs = ['username', 'password', 'change_pass', 'profile_image'];

                    if (!$modelValid) {
                        $modelErrors = $model->getErrors();
                        if (count(array_intersect_key($modelErrors, array_flip($personalDetailsAttrs))) > 0) {
                            $stepRenderData['errorStepIndex'] = array_search(self::STEP_PERSONAL_DETAILS, $this->_steps);
                        } elseif (count(array_intersect_key($modelErrors, array_flip($accountSettingsAttrs))) > 0) {
                             $stepRenderData['errorStepIndex'] = array_search(self::STEP_ACCOUNT_SETTINGS, $this->_steps);
                        }
                        // If $model errors don't match specific steps, errorStepIndex remains null for now
                    }

                    if (!$appApplicantModelValid) {
                        $appErrors = $appApplicantModel->getErrors();
                        // If errorStepIndex is not already set by $model errors, or if $appApplicantModel errors are for its specific step
                        if (is_null($stepRenderData['errorStepIndex']) && count(array_intersect_key($appErrors, array_flip($applicantSpecificsAttrs))) > 0) {
                            $stepRenderData['errorStepIndex'] = array_search(self::STEP_APPLICANT_SPECIFICS, $this->_steps);
                        }
                    }

                    // Fallback if no specific error step determined, or set currentStep based on determined index
                    if (is_null($stepRenderData['errorStepIndex'])) {
                        $stepRenderData['errorStepIndex'] = 0; // Default to first tab
                    }
                    $currentStep = $this->_steps[$stepRenderData['errorStepIndex']];
                }
            }
        }

        // For GET requests or if POST but redirecting back with errors:
        // Ensure the $currentStep (for JS navigation target) is correctly set.
        // If $stepRenderData['errorStepIndex'] is set, that's the target.
        // Otherwise, $currentStep determined at the beginning of the action is used.
        $targetStepForJs = $stepRenderData['errorStepIndex'] !== null ? $this->_steps[$stepRenderData['errorStepIndex']] : $currentStep;

        // If scenarios were used for GET to help client-side validation, they might still be useful
        // However, with a single form, client-side validation should ideally work across all fields
        // based on the model's overall rules active for the current scenario (e.g., SCENARIO_DEFAULT).
        // For simplicity, we might rely on server-side validation for now.
        // if ($targetStepForJs === self::STEP_PERSONAL_DETAILS) {
        // $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
        // } elseif ($targetStepForJs === self::STEP_ACCOUNT_SETTINGS) {
        // $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
        // }


        return $this->render('update-wizard', [
            'currentStep' => $targetStepForJs, // This tells JS which tab to open
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
