<?php

use app\models\AppContactTypes;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var app\models\search\ContactTypesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Contact Types';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="app-contact-types-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Contact Types', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); 
    ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'contact_type_id',
            'contact_type_name',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, AppContactTypes $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'contact_type_id' => $model->contact_type_id]);
                }
            ],
        ],
    ]); ?>


</div>