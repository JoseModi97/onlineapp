<?php

namespace app\controllers;

use app\models\AppApplicationIntake;
use app\models\search\ApplicationIntakeSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response; // Added
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicationIntakeController implements the CRUD actions for AppApplicationIntake model.
 */
class ApplicationIntakeController extends Controller
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
     * Lists all AppApplicationIntake models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicationIntakeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicationIntake model.
     * @param int $intake_code Intake Code
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($intake_code)
    {
        return $this->render('view', [
            'model' => $this->findModel($intake_code),
        ]);
    }

    /**
     * Creates a new AppApplicationIntake model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicationIntake();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'intake_code' => $model->intake_code]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplicationIntake model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $intake_code Intake Code
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($intake_code)
    {
        $model = $this->findModel($intake_code);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'intake_code' => $model->intake_code]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplicationIntake model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $intake_code Intake Code
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($intake_code)
    {
        $this->findModel($intake_code)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplicationIntake model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $intake_code Intake Code
     * @return AppApplicationIntake the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($intake_code)
    {
        if (($model = AppApplicationIntake::findOne(['intake_code' => $intake_code])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionApplicationIntakeList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $data = AppApplicationIntake::find()
                ->select(['id' => 'intake_code', 'text' => 'intake_name'])
                ->where(['like', 'intake_name', $q])
                ->limit(20)
                ->asArray()
                ->all();
            $out['results'] = $data;
        }
        return $out;
    }
}
