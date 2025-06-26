<?php

use app\models\AppApplicantUser;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\AppApplicantUserSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'App Applicant Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create App Applicant User', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'applicant_user_id',
            'surname',
            'other_name',
            'email_address:email',
            'country_code',
            //'mobile_no',
            //'password',
            //'activation_code',
            //'salt',
            //'status',
            //'date_registered',
            //'reg_token',
            //'profile_image',
            //'first_name',
            //'change_pass',
            //'username',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicantUser $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'applicant_user_id' => $model->applicant_user_id]);
                 }
            ],
        ],
    ]); ?>


</div>
