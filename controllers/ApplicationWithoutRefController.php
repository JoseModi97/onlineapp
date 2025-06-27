<?php

namespace app\controllers;

use app\models\AppApplicationWithoutRef;
use app\models\search\ApplicationWithoutRefSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicationWithoutRefController implements the CRUD actions for AppApplicationWithoutRef model.
 */
class ApplicationWithoutRefController extends Controller
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
     * Lists all AppApplicationWithoutRef models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicationWithoutRefSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicationWithoutRef model.
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
     * Creates a new AppApplicationWithoutRef model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicationWithoutRef();

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
     * Updates an existing AppApplicationWithoutRef model.
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
     * Deletes an existing AppApplicationWithoutRef model.
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
     * Finds the AppApplicationWithoutRef model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $application_id Application ID
     * @return AppApplicationWithoutRef the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($application_id)
    {
        if (($model = AppApplicationWithoutRef::findOne(['application_id' => $application_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
