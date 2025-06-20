<?php

use app\models\AppApplicantContacts;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ApplicantContactsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Applicant Contacts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-applicant-contacts-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Applicant Contacts', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'contact_id',
            'applicant_id',
            'contact_type_id',
            'full_names',
            'calling_code',
            //'mobile_no',
            //'email_address:email',
            //'postal_address',
            //'postal_code',
            //'town',
            //'country_code',
            //'primary_contact',
            //'relationship',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppApplicantContacts $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'contact_id' => $model->contact_id]);
                }
            ],
        ],
    ]); ?>


</div>