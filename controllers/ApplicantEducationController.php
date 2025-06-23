<?php

namespace app\controllers;

use app\models\AppApplicantEducation;
use app\models\search\ApplicantEducationSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicantEducationController implements the CRUD actions for AppApplicantEducation model.
 */
class ApplicantEducationController extends Controller
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
     * Lists all AppApplicantEducation models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicantEducationSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicantEducation model.
     * @param int $education_id Education ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($education_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($education_id),
        ]);
    }

    /**
     * Creates a new AppApplicantEducation model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicantEducation();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'education_id' => $model->education_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplicantEducation model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $education_id Education ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($education_id)
    {
        $model = $this->findModel($education_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'education_id' => $model->education_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplicantEducation model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $education_id Education ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($education_id)
    {
        $this->findModel($education_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplicantEducation model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $education_id Education ID
     * @return AppApplicantEducation the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($education_id)
    {
        if (($model = AppApplicantEducation::findOne(['education_id' => $education_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
