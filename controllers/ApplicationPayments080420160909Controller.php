<?php

namespace app\controllers;

use app\models\AppApplicationPayments080420160909;
use app\models\search\ApplicationPayments080420160909Search;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicationPayments080420160909Controller implements the CRUD actions for AppApplicationPayments080420160909 model.
 */
class ApplicationPayments080420160909Controller extends Controller
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
     * Lists all AppApplicationPayments080420160909 models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicationPayments080420160909Search();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicationPayments080420160909 model.
     * @param int $payment_id Payment ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($payment_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($payment_id),
        ]);
    }

    /**
     * Creates a new AppApplicationPayments080420160909 model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicationPayments080420160909();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'payment_id' => $model->payment_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplicationPayments080420160909 model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $payment_id Payment ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($payment_id)
    {
        $model = $this->findModel($payment_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'payment_id' => $model->payment_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplicationPayments080420160909 model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $payment_id Payment ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($payment_id)
    {
        $this->findModel($payment_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplicationPayments080420160909 model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $payment_id Payment ID
     * @return AppApplicationPayments080420160909 the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($payment_id)
    {
        if (($model = AppApplicationPayments080420160909::findOne(['payment_id' => $payment_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
