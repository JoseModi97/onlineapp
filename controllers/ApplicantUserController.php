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
    public function actionUpdateWizard($applicant_user_id)
    {
        $model = $this->findModel($applicant_user_id);
        $appApplicantModel = $model->getAppApplicant()->one();

        if (!$appApplicantModel) {
            $appApplicantModel = new AppApplicant();
            $appApplicantModel->applicant_user_id = $model->applicant_user_id;
        }

        if ($this->request->isPost) {
            $modelLoaded = $model->load($this->request->post());
            $appApplicantModelLoaded = $appApplicantModel->load($this->request->post());

            if ($modelLoaded && $appApplicantModelLoaded) {
                $validUser = $model->validate();
                $validApplicant = $appApplicantModel->validate();

                if ($validUser && $validApplicant) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        if ($model->save(false)) {
                            $appApplicantModel->applicant_user_id = $model->applicant_user_id; // Ensure it's set
                            if ($appApplicantModel->save(false)) {
                                $transaction->commit();
                                Yii::$app->session->setFlash('success', 'Applicant details updated successfully.');
                                return $this->redirect(['view', 'applicant_user_id' => $model->applicant_user_id]);
                            }
                        }
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'Error saving applicant details.');
                    } catch (\Exception $e) {
                        $transaction->rollBack();
                        Yii::$app->session->setFlash('error', 'An error occurred: ' . $e->getMessage());
                    }
                } else {
                    // Combine errors for a more descriptive flash message
                    $errorMessages = [];
                    foreach ($model->getErrors() as $attribute => $errors) {
                        $errorMessages[] = $model->getAttributeLabel($attribute) . ': ' . implode(', ', $errors);
                    }
                    foreach ($appApplicantModel->getErrors() as $attribute => $errors) {
                        $errorMessages[] = $appApplicantModel->getAttributeLabel($attribute) . ': ' . implode(', ', $errors);
                    }
                    $flashMessage = 'Validation failed. Please check the following: <br/>' . implode('<br/>', $errorMessages);
                    Yii::$app->session->setFlash('error', $flashMessage);
                }
            }
        }

        return $this->render('update-wizard', [
            'model' => $model,
            'appApplicantModel' => $appApplicantModel,
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
