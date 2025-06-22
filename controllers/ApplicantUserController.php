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
                'wizard' => [
                    'class' => WizardBehavior::class,
                    'steps' => ['personal-details', 'applicant-specifics', 'account-settings'],
                    'events' => [
                        WizardBehavior::EVENT_WIZARD_STEP => [$this, 'handleWizardStep'],
                        WizardBehavior::EVENT_AFTER_WIZARD => [$this, 'handleAfterWizard'],
                        // TODO: Consider adding handlers for EVENT_INVALID_STEP if needed
                    ],
                    'sessionKey' => 'applicantUserWizard', // Optional: custom session key
                    // redirectUrl will be handled by afterWizard event handler on success
                    // It can be a fallback if afterWizard does not set $event->continue = false
                    'redirectUrl' => ['applicant-user/index'],
                ]
            ]
        );
    }

    public function actionUpdateWizard($step = null, $applicant_user_id = null)
    {
        // If applicant_user_id is provided in URL and it's the first time, store it in session for the behavior.
        // The behavior itself primarily uses its own session storage after the first step or if data is POSTed.
        if ($applicant_user_id !== null && !$this->wizard->hasStarted()) {
             // Check if we are starting the wizard for a specific user
            $this->wizard->save($this->wizard->sessionKey . '.applicant_user_id', $applicant_user_id);
        }

        // The WizardBehavior's step() method will handle the actual logic,
        // including rendering the view for the current step or processing POST data.
        // It uses $step from the URL query parameter (or defaults if not set).
        $data = $this->wizard->step($step);

        if ($data instanceof \yii\web\Response) { // If step() returns a Response, it's a redirect
            return $data;
        }

        // If $data is not a Response, it's typically the array of data to be passed to the view.
        // The view rendering should be handled by the WizardBehavior when it processes EVENT_WIZARD_STEP
        // and the event handler (handleWizardStep) does not set $event->handled = true without a redirect.
        // However, the behavior itself doesn't render a "wrapper" view, only the step's content.
        // The event handler ($this->handleWizardStep) sets $event->data which is then available in the step view.
        // The main wizard view ('update-wizard.php') should be rendered by this action if not redirected.

        // If $step is null and wizard has not completed, it will redirect to the first step.
        // If $step is set, and it's a valid step, handleWizardStep will prepare $event->data.
        // We need to render the main wizard layout view, which in turn renders the specific step view.

        // The WizardBehavior's step() method, when not redirecting, will return the data
        // from the event handler if $event->handled was false, or proceed to next step (redirect)
        // if $event->handled was true.
        // If it returns data (meaning $event->handled was false, we are staying on the current step),
        // we need to render our main wizard view which then renders the step view.

        // The event object is not directly available here to pass to the main view.
        // The main view needs the current step name and the WizardBehavior instance.
        // The WizardBehavior itself should be accessible via $this->wizard.
        // The current step is $this->wizard->getCurrentStep() or $step if provided.

        // It's better to let the EVENT_WIZARD_STEP handler prepare all necessary data
        // and the controller action just renders the main wizard view if no redirect occurred.
        // The view 'update-wizard' will then use $this->wizard (the behavior instance)
        // to get step information and render the appropriate step view.

        if (is_array($data)) { // Data returned from wizard an event handler (e.g. form to display)
             return $this->render('update-wizard', [
                'event' => $this->wizard->getLastEvent(), // Pass the last event to the view
                // 'model' and 'appApplicantModel' are inside $event->data as set in handleWizardStep
            ]);
        }
        // If $data is null, it might mean the wizard completed and redirected via its own logic,
        // or some other flow occurred. If it's a Response, it was already returned.
        // If after all processing, $data is something else or null and no redirect happened,
        // this might indicate an issue or an unhandled wizard state.
        // For now, assume that if it's not a Response, it's data for the view.
        // A null might mean the wizard finished and the redirectUrl from behavior config took over.
        return $data; // This could be a rendered view string from a step or other data.
    }

    // Renamed from wizardStep to handleWizardStep
    public function handleWizardStep($event)
    {
        $session = Yii::$app->session;
        $applicant_user_id = Yii::$app->request->get('applicant_user_id', $session->get('wizard_applicant_user_id'));

        if (empty($applicant_user_id) && $event->step !== 'personal-details') {
            $event->data = ['message' => 'Applicant User ID is missing or session expired. Please start over.'];
            $this->wizard->resetWizard(); // Resets behavior's internal session data
            $session->remove('wizard_applicant_user_id'); // Clear our specific session data
            $session->remove('wizard_app_applicant_attributes');
            $session->remove('wizard_account_attributes');
            $event->sender->owner->redirect(['update-wizard', $this->wizard->queryParam => $this->wizard->steps[0]]);
            $event->handled = true;
            return;
        }

        $model = $applicant_user_id ? $this->findModel($applicant_user_id) : new AppApplicantUser();
        // Load AppApplicant attributes from session if they exist, for repopulating form on prev/next
        $appApplicantSessionAttributes = $session->get('wizard_app_applicant_attributes', []);
        $appApplicantModel = $model->isNewRecord ? new AppApplicant() : ($model->getAppApplicant()->one() ?? new AppApplicant());
        if (!empty($appApplicantSessionAttributes) && $event->step === 'applicant-specifics') {
            $appApplicantModel->setAttributes($appApplicantSessionAttributes, false);
        }
        // Load Account attributes from session for repopulating form
        $accountSessionAttributes = $session->get('wizard_account_attributes', []);
         if (!empty($accountSessionAttributes) && $event->step === 'account-settings') {
            $model->setAttributes($accountSessionAttributes, false); // Apply to current model instance for the form
        }


        if ($model->isNewRecord && $event->step !== 'personal-details') {
            $event->data = ['message' => 'Please complete the first step.'];
            $this->wizard->resetWizard();
            $session->remove('wizard_applicant_user_id');
            $event->sender->owner->redirect(['update-wizard', $this->wizard->queryParam => $this->wizard->steps[0]]);
            $event->handled = true;
            return;
        }

        $event->data = [ // This data is primarily for the current step's view rendering
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
        ];

        if (Yii::$app->request->isPost) {
            // Handle Cancel button
            if (Yii::$app->request->post('wizard_cancel')) {
                $event->continue = false; // Stop the wizard
                $event->handled = false;  // Indicate no data was processed or saved
                $this->wizard->resetWizard();
                Yii::$app->session->remove('wizard_applicant_user_id');
                Yii::$app->session->remove('wizard_app_applicant_attributes');
                Yii::$app->session->remove('wizard_account_attributes');
                // Redirect to a relevant page, e.g., index or a specific cancel URL
                $event->sender->owner->redirect(['index']);
                return; // Return early
            }

            // Handle Previous button
            if (Yii::$app->request->post('wizard_previous')) {
                $event->nextStep = WizardBehavior::DIRECTION_BACKWARD;
                $event->handled = true; // Tell the behavior to process this direction
                // No data loading or validation is typically needed when going back
                return; // Return early
            }

            // Proceed with Next or Save button logic (data loading and validation)
            $isModelLoaded = false;
            $isAppApplicantModelLoaded = false;

            if ($event->step === 'personal-details') {
                $isModelLoaded = $model->load(Yii::$app->request->post());
            } elseif ($event->step === 'applicant-specifics') {
                $isAppApplicantModelLoaded = $appApplicantModel->load(Yii::$app->request->post());
            } elseif ($event->step === 'account-settings') {
                $isModelLoaded = $model->load(Yii::$app->request->post());
            }

            if ($isModelLoaded || $isAppApplicantModelLoaded) {
                $isValid = true;
                if ($event->step === 'personal-details') {
                    $model->scenario = AppApplicantUser::SCENARIO_STEP_PERSONAL_DETAILS;
                    $isValid = $model->validate();
                    if ($isValid) {
                        if ($model->save(false)) {
                            $session->set('wizard_applicant_user_id', $model->applicant_user_id);
                        } else {
                             $isValid = false;
                        }
                    }
                } elseif ($event->step === 'applicant-specifics') {
                    $current_applicant_user_id = $session->get('wizard_applicant_user_id');
                    if ($appApplicantModel->isNewRecord && $current_applicant_user_id) {
                        $appApplicantModel->applicant_user_id = $current_applicant_user_id;
                    }
                    $isValid = $appApplicantModel->validate();
                     if ($isValid) {
                         $session->set('wizard_app_applicant_attributes', $appApplicantModel->getAttributes());
                    }
                } elseif ($event->step === 'account-settings') {
                    $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS;
                    $isValid = $model->validate();
                     if ($isValid) {
                        $session->set('wizard_account_attributes', $model->getAttributes(['username', 'password', 'profile_image', 'change_pass']));
                    }
                }

                if ($isValid) {
                    $event->handled = true;
                } else {
                    $event->data['message'] = 'Please correct the errors below.';
                    // Behavior will stay on current step as $event->handled is false
                }
            }
        }
    }

    // Renamed from afterWizard to handleAfterWizard
    public function handleAfterWizard($event)
    {
        $session = Yii::$app->session;
        $applicant_user_id = $session->get('wizard_applicant_user_id');
        $appApplicantModelAttributes = $session->get('wizard_app_applicant_attributes', []);
        $modelAccountAttributes = $session->get('wizard_account_attributes', []);

        // Clean up session data for the wizard
        $session->remove('wizard_applicant_user_id');
        $session->remove('wizard_app_applicant_attributes');
        $session->remove('wizard_account_attributes');

        if (!$applicant_user_id) {
            Yii::$app->session->setFlash('error', 'Applicant user ID not found in session. Cannot complete the process.');
            $event->sender->owner->redirect(['update-wizard', $this->wizard->queryParam => $this->wizard->steps[0]]);
            $event->continue = false; // Stop wizard progression
            return;
        }

        $model = $this->findModel($applicant_user_id);
        // Apply account settings attributes
        if ($modelAccountAttributes) {
            $model->setAttributes($modelAccountAttributes, false); // false to avoid mass assignment issues if scenarios are strict
        }

        $appApplicantModel = $model->getAppApplicant()->one() ?? new AppApplicant();
        if ($appApplicantModelAttributes) {
            $appApplicantModel->setAttributes($appApplicantModelAttributes);
        }

        $transaction = Yii::$app->db->beginTransaction();
        try {
            // Model's personal details were already saved in the first step.
            // Now save the model with account settings and then the applicant specifics.
            $model->scenario = AppApplicantUser::SCENARIO_STEP_ACCOUNT_SETTINGS; // ensure correct scenario for final save if needed
            if ($model->save()) { // This will save attributes including those from account settings step
                $appApplicantModel->applicant_user_id = $model->applicant_user_id;
                if ($appApplicantModel->save()) {
                    $transaction->commit();
                    Yii::$app->session->setFlash('success', 'Applicant details saved successfully.');
                    $event->sender->owner->redirect(['view', 'applicant_user_id' => $model->applicant_user_id]);
                    $event->continue = false; // Explicitly state wizard cycle is done and redirect handled.
                } else {
                    $transaction->rollBack();
                    Yii::$app->session->setFlash('error', 'Error saving applicant specifics: ' . print_r($appApplicantModel->getErrors(), true));
                    $event->continue = false; // Stop wizard progression
                    // Allow rendering current step view with error by not redirecting here
                    // Or redirect to the specific step if desired:
                    // $event->sender->owner->redirect(['update-wizard', $this->wizard->queryParam => $this->wizard->steps[count($this->wizard->steps)-1], 'applicant_user_id' => $applicant_user_id]);
                }
            } else {
                $transaction->rollBack();
                Yii::$app->session->setFlash('error', 'Error saving applicant account details: ' . print_r($model->getErrors(), true));
                $event->continue = false; // Stop wizard progression
                 // $event->sender->owner->redirect(['update-wizard', $this->wizard->queryParam => $this->wizard->steps[count($this->wizard->steps)-1], 'applicant_user_id' => $applicant_user_id]);
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::$app->session->setFlash('error', 'An error occurred: ' . $e->getMessage());
            $event->continue = false; // Stop wizard progression
            // $event->sender->owner->redirect(['update-wizard', $this->wizard->queryParam => $this->wizard->steps[0], 'applicant_user_id' => $applicant_user_id]);
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
