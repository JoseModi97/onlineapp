<?php

namespace app\controllers;

use Yii;
use app\models\AppEducationSystem;
use yii\web\Controller;
use yii\web\Response;

class EducationSystemController extends Controller
{
    public function actionEducationSystemList($q = null)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        $out = ['results' => ['id' => '', 'text' => '']];
        if (!is_null($q)) {
            $data = AppEducationSystem::find()
                ->select(['id' => 'edu_system_code', 'text' => 'edu_system_name'])
                ->where(['like', 'edu_system_name', $q])
                ->limit(20)
                ->asArray()
                ->all();
            $out['results'] = $data;
        }
        return $out;
    }
}
