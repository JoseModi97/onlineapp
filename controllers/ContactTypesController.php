<?php

namespace app\controllers;

use app\models\AppContactTypes;
use app\models\search\ContactTypesSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response; // Added
use yii\filters\VerbFilter;
use app\components\AccessControlBehavior;

/**
 * ContactTypesController implements the CRUD actions for AppContactTypes model.
 */
class ContactTypesController extends Controller
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
     * Lists all AppContactTypes models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ContactTypesSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AppContactTypes model.
     * @param int $contact_type_id Contact Type ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($contact_type_id)
    {
        return $this->render('view', [
            'model' => $this->findModel($contact_type_id),
        ]);
    }

    /**
     * Creates a new AppContactTypes model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new AppContactTypes();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'contact_type_id' => $model->contact_type_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AppContactTypes model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $contact_type_id Contact Type ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($contact_type_id)
    {
        $model = $this->findModel($contact_type_id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'contact_type_id' => $model->contact_type_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing AppContactTypes model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $contact_type_id Contact Type ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($contact_type_id)
    {
        $this->findModel($contact_type_id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the AppContactTypes model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $contact_type_id Contact Type ID
     * @return AppContactTypes the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($contact_type_id)
    {
        if (($model = AppContactTypes::findOne(['contact_type_id' => $contact_type_id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionContactTypeList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $data = AppContactTypes::find()
                ->select(['id' => 'contact_type_id', 'text' => 'contact_type_name'])
                ->where(['like', 'contact_type_name', $q])
                ->limit(20)
                ->asArray()
                ->all();
            $out['results'] = $data;
        }
        return $out;
    }
}
