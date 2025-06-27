<?php

namespace app\controllers;

use app\models\AppApplicationTracking;
use app\models\search\ApplicationTrackingSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicationTrackingController implements the CRUD actions for AppApplicationTracking model.
 */
class ApplicationTrackingController extends Controller
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
     * Lists all AppApplicationTracking models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicationTrackingSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicationTracking model.
     * @param int $tracking_id Tracking ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($tracking_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($tracking_id),
        ]);
    }

    /**
     * Creates a new AppApplicationTracking model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicationTracking();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'tracking_id' => $model->tracking_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplicationTracking model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $tracking_id Tracking ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($tracking_id)
    {
        $model = $this->findModel($tracking_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'tracking_id' => $model->tracking_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplicationTracking model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $tracking_id Tracking ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($tracking_id)
    {
        $this->findModel($tracking_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplicationTracking model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $tracking_id Tracking ID
     * @return AppApplicationTracking the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($tracking_id)
    {
        if (($model = AppApplicationTracking::findOne(['tracking_id' => $tracking_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
