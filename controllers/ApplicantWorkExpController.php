<?php

namespace app\controllers;

use app\models\AppApplicantWorkExp;
use app\models\search\ApplicantWorkExpSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicantWorkExpController implements the CRUD actions for AppApplicantWorkExp model.
 */
class ApplicantWorkExpController extends Controller
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
     * Lists all AppApplicantWorkExp models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicantWorkExpSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicantWorkExp model.
     * @param int $experience_id Experience ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($experience_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($experience_id),
        ]);
    }

    /**
     * Creates a new AppApplicantWorkExp model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicantWorkExp();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'experience_id' => $model->experience_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplicantWorkExp model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $experience_id Experience ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($experience_id)
    {
        $model = $this->findModel($experience_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'experience_id' => $model->experience_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplicantWorkExp model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $experience_id Experience ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($experience_id)
    {
        $this->findModel($experience_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplicantWorkExp model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $experience_id Experience ID
     * @return AppApplicantWorkExp the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($experience_id)
    {
        if (($model = AppApplicantWorkExp::findOne(['experience_id' => $experience_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
