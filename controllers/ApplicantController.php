<?php

namespace app\controllers;

use app\models\AppApplicant;
use app\models\AppApplicantUser; // Added
use app\models\search\ApplicantSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicantController implements the CRUD actions for AppApplicant model.
 */
class ApplicantController extends Controller
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
     * Lists all AppApplicant models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicantSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicant model.
     * @param int $applicant_id Applicant ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($applicant_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($applicant_id),
        ]);
    }

    /**
     * Creates a new AppApplicant model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicant();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'applicant_id' => $model->applicant_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplicant model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $applicant_id Applicant ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($applicant_id)
    {
        $model = $this->findModel($applicant_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'applicant_id' => $model->applicant_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplicant model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $applicant_id Applicant ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($applicant_id)
    {
        $this->findModel($applicant_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplicant model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $applicant_id Applicant ID
     * @return AppApplicant the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($applicant_id)
    {
        if (($model = AppApplicant::findOne(['applicant_id' => $applicant_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionApplicantList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $query = new \yii\db\Query();
            $query->select(['app_applicant.applicant_id AS id', 'text' => "CONCAT('ID: ', app_applicant.applicant_id, ' - ', app_applicant_user.first_name, ' ', app_applicant_user.last_name)"])
                ->from('onlineapp.app_applicant app_applicant')
                ->leftJoin('onlineapp.app_applicant_user app_applicant_user', 'app_applicant.applicant_user_id = app_applicant_user.applicant_user_id')
                ->where(['like', 'app_applicant.applicant_id', $q])
                ->orWhere(['like', 'app_applicant_user.first_name', $q])
                ->orWhere(['like', 'app_applicant_user.last_name', $q]) // Changed surname to last_name
                ->limit(20);
            $out['results'] = $query->all();
        }
        return $out;
    }
}
