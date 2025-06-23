<?php

namespace app\controllers;

use app\models\AppApplicantContacts;
use app\models\search\ApplicantContactsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ApplicantContactsController implements the CRUD actions for AppApplicantContacts model.
 */
class ApplicantContactsController extends Controller
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
     * Lists all AppApplicantContacts models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ApplicantContactsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppApplicantContacts model.
     * @param int $contact_id Contact ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($contact_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($contact_id),
        ]);
    }

    /**
     * Creates a new AppApplicantContacts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppApplicantContacts();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'contact_id' => $model->contact_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppApplicantContacts model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $contact_id Contact ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($contact_id)
    {
        $model = $this->findModel($contact_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'contact_id' => $model->contact_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppApplicantContacts model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $contact_id Contact ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($contact_id)
    {
        $this->findModel($contact_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppApplicantContacts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $contact_id Contact ID
     * @return AppApplicantContacts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($contact_id)
    {
        if (($model = AppApplicantContacts::findOne(['contact_id' => $contact_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
