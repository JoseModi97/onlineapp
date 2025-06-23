<?php

namespace app\controllers;

use app\models\AppNotifications;
use app\models\search\NotificationsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * NotificationsController implements the CRUD actions for AppNotifications model.
 */
class NotificationsController extends Controller
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
     * Lists all AppNotifications models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new NotificationsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppNotifications model.
     * @param int $notification_id Notification ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($notification_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($notification_id),
        ]);
    }

    /**
     * Creates a new AppNotifications model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppNotifications();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'notification_id' => $model->notification_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppNotifications model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $notification_id Notification ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($notification_id)
    {
        $model = $this->findModel($notification_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'notification_id' => $model->notification_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppNotifications model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $notification_id Notification ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($notification_id)
    {
        $this->findModel($notification_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppNotifications model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $notification_id Notification ID
     * @return AppNotifications the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($notification_id)
    {
        if (($model = AppNotifications::findOne(['notification_id' => $notification_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
