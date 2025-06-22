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
use beastbytes\wizard\WizardBehavior;
use yii\helpers\ArrayHelper;

class ApplicantUserController extends Controller
{
    public function actions()
    {
        return [
            'update-wizard' => [
                'class' => 'beastbytes\wizard\WizardAction',
                'steps' => ['personal-details', 'applicant-specifics', 'account-settings'],
                'events' => [
                    WizardBehavior::EVENT_WIZARD_STEP => [$this, 'wizardStep'],
                    WizardBehavior::EVENT_AFTER_WIZARD => [$this, 'afterWizard'],
                ],
                'redirectUrl' => function ($wizard) {
                    // Ensure applicant_user_id is read from session, as it's stored there
                    $applicantUserId = $wizard->readFromSession('applicant_user_id');
                    if ($applicantUserId) {
                        return ['view', 'applicant_user_id' => $applicantUserId];
                    }
                    // Fallback or error handling if ID is not found, though afterWizard should ensure it
                    return ['index']; // Or some other appropriate default
                }
            ],
        ];
    }

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
                    ],
                ],
            ]
        );
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
    /**
     * Updates an existing AppApplicantUser model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $applicant_user_id Applicant User ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function wizardStep($event)
    {
        $applicant_user_id = Yii::$app->request->get('applicant_user_id');
        if (empty($applicant_user_id) && $event->step !== 'personal-details') {
             // if applicant_user_id is not set and not on the first step, redirect to the first step or an error page
            $event->data = ['message' => 'Applicant User ID is missing.'];
            $event->action->resetWizard();
            $event->action->redirect = ['update-wizard', 'step' => 'personal-details'];
            $event->handled = true;
            return;
        }

        $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        $appApplicantModel = $model->isNewRecord ? new AppApplicant() : ($model->getAppApplicant()->one() ?? new AppApplicant());

        if ($model->isNewRecord && $event->step !== 'personal-details') {
            // If it's a new record and not the first step, something is wrong.
            // Potentially redirect to the first step or show an error.
            // This might happen if the user tries to access a later step directly via URL for a new record.
            $event->data = ['message' => 'Please complete the first step.'];
            $event->action->resetWizard();
            $event->action->redirect = ['update-wizard', 'step' => 'personal-details'];
            $event->handled = true;
            return;
        }

        $event->data = [
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
        ];

        if (Yii::$app->request->isPost) {
            $isModelLoaded = false;
            $isAppApplicantModelLoaded = false;

            if ($event->step === 'personal-details' || $event->step === 'account-settings') {
                $isModelLoaded = $model->load(Yii::$app->request->post());
            }
            if ($event->step === 'applicant-specifics') {
                $isAppApplicantModelLoaded = $appApplicantModel->load(Yii::$app->request->post());
            }

            if ($isModelLoaded || $isAppApplicantModelLoaded) {
                $isValid = true;
                if ($event->step === 'personal-details') {
                    $isValid = $model->validate(AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS);
                     if ($isValid && $model->isNewRecord) { // Save after first step if new
                        if ($model->save(false)) {
                            $event->action->saveToSession('applicant_user_id', $model->applicant_user_id);
                        } else {
                            $isValid = false;
                        }
                    } elseif ($isValid && !$model->isNewRecord) { // Update if existing
                        $model->save(false);
                    }
                } elseif ($event->step === 'applicant-specifics') {
                    $appApplicantModel->applicant_user_id = $event->action->readFromSession('applicant_user_id');
                    $isValid = $appApplicantModel->validate();
                } elseif ($event->step === 'account-settings') {
                    $isValid = $model->validate(AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS);
                }

                if ($isValid) {
                    $event->action->saveToSession('modelAttributes', $model->getAttributes());
                    if ($appApplicantModel) {
                        $event->action->saveToSession('appApplicantModelAttributes', $appApplicantModel->getAttributes());
                    }
                    $event->handled = true; // Proceed to next step
                } else {
                    // Validation failed, stay on current step and display errors
                    $event->data['message'] = 'Please correct the errors below.';
                    $event->action->stay(); // This will re-render the current step's view
                }
            }
        }
    }

    public function afterWizard($event)
    {
        $modelAttributes = $event->action->readFromSession('modelAttributes', []);
        $appApplicantModelAttributes = $event->action->readFromSession('appApplicantModelAttributes', []);
        $applicant_user_id = $event->action->readFromSession('applicant_user_id');

        if (!$applicant_user_id) {
            Yii::$app->session->setFlash('error', 'Applicant user ID not found in session. Cannot save.');
            $event->action->redirect = ['update-wizard', 'step' => $event->action->steps[0]]; // Redirect to first step
            return;
        }

        $model = $this->findModel($applicant_user_id);
        if (!$model) { // Should not happen if findModel throws exception for not found
            $model = new AppApplicantUser();
            // If we were creating a new user and saving ID only after first step,
            // this logic would need to handle new AppApplicantUser() creation carefully.
            // However, current logic saves new AppApplicantUser in the first step if it's new.
        } else {
            $model = new AppApplicantUser();
        }

        $model->setAttributes($modelAttributes);

        $appApplicantModel = $model->isNewRecord ? new AppApplicant() : ($model->getAppApplicant()->one() ?? new AppApplicant());
        if ($appApplicantModelAttributes) {
            $appApplicantModel->setAttributes($appApplicantModelAttributes);
        }

        // Final save operation
        $transaction = Yii::$app->db->beginTransaction();
        try {
            if ($model->save()) {
                $appApplicantModel->applicant_user_id = $model->applicant_user_id;
                if ($appApplicantModel->save()) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Applicant details saved successfully.');
                    $event->action->redirect = ['view', 'applicant_user_id' => $model->applicant_user_id];
                } else {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Error saving applicant specifics: ' . print_r($appApplicantModel->getErrors(), true));
                    $event->action->redirect = ['update-wizard', 'applicant_user_id' => $model->applicant_user_id, 'step' => $event->action->steps[count($event->action->steps)-1]]; // Redirect to last step
                }
            } else {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error saving applicant user details: ' . print_r($model->getErrors(), true));
                $event->action->redirect = ['update-wizard', 'applicant_user_id' => $model->applicant_user_id, 'step' => $event->action->steps[0]]; // Redirect to first step
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $event->action->redirect = ['update-wizard', 'applicant_user_id' => $model->applicant_user_id, 'step' => $event->action->steps[0]];
        }
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
