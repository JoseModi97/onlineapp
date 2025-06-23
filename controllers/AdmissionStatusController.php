<?php

namespace app\controllers;

use app\models\AppAdmissionStatus;
use app\models\search\AdmissionStatusSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response; // Added
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * AdmissionStatusController implements the CRUD actions for AppAdmissionStatus model.
 */
class AdmissionStatusController extends Controller
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
     * Lists all AppAdmissionStatus models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AdmissionStatusSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppAdmissionStatus model.
     * @param int $status_id Status ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($status_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($status_id),
        ]);
    }

    /**
     * Creates a new AppAdmissionStatus model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppAdmissionStatus();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'status_id' => $model->status_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppAdmissionStatus model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $status_id Status ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($status_id)
    {
        $model = $this->findModel($status_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'status_id' => $model->status_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppAdmissionStatus model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $status_id Status ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($status_id)
    {
        $this->findModel($status_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppAdmissionStatus model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $status_id Status ID
     * @return AppAdmissionStatus the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($status_id)
    {
        if (($model = AppAdmissionStatus::findOne(['status_id' => $status_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionAdmissionStatusList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $data = AppAdmissionStatus::find()
                ->select(['id' => 'status_id', 'text' => 'status_name'])
                ->where(['like', 'status_name', $q])
                ->limit(20)
                ->asArray()
                ->all();
            $out['results'] = $data;
        }
        return $out;
    }
}
