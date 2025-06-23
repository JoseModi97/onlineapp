<?php

namespace app\controllers;

use app\models\AppApplicationFees;
use app\models\search\ApplicationFeesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response; // Added
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicationFeesController implements the CRUD actions for AppApplicationFees model.
 */
class ApplicationFeesController extends Controller
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
     * Lists all AppApplicationFees models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicationFeesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicationFees model.
     * @param int $application_fee_id Application Fee ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($application_fee_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($application_fee_id),
        ]);
    }

    /**
     * Creates a new AppApplicationFees model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicationFees();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'application_fee_id' => $model->application_fee_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplicationFees model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $application_fee_id Application Fee ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($application_fee_id)
    {
        $model = $this->findModel($application_fee_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'application_fee_id' => $model->application_fee_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplicationFees model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $application_fee_id Application Fee ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($application_fee_id)
    {
        $this->findModel($application_fee_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplicationFees model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $application_fee_id Application Fee ID
     * @return AppApplicationFees the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($application_fee_id)
    {
        if (($model = AppApplicationFees::findOne(['application_fee_id' => $application_fee_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionApplicationFeesList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $data = AppApplicationFees::find()
                ->select(['id' => 'application_fee_id', 'text' => "CONCAT(programme_type, ' - ', amount, ' ', currency)"])
                ->where(['like', 'programme_type', $q])
                ->limit(20)
                ->asArray()
                ->all();
            $out['results'] = $data;
        }
        return $out;
    }
}
