<?php

namespace app\controllers;

use app\models\AppApplication080420160909;
use app\models\search\AppApplication080420160909Search;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * AppApplication080420160909Controller implements the CRUD actions for AppApplication080420160909 model.
 */
class AppApplication080420160909Controller extends Controller
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
     * Lists all AppApplication080420160909 models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AppApplication080420160909Search();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplication080420160909 model.
     * @param int $application_id Application ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($application_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($application_id),
        ]);
    }

    /**
     * Creates a new AppApplication080420160909 model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplication080420160909();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'application_id' => $model->application_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplication080420160909 model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $application_id Application ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($application_id)
    {
        $model = $this->findModel($application_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'application_id' => $model->application_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplication080420160909 model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $application_id Application ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($application_id)
    {
        $this->findModel($application_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplication080420160909 model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $application_id Application ID
     * @return AppApplication080420160909 the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($application_id)
    {
        if (($model = AppApplication080420160909::findOne(['application_id' => $application_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
