<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantUser $model */

$this->title = $model->applicant_user_id;
$this->params['breadcrumbs'][] = ['label' => 'App Applicant Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-applicant-user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'applicant_user_id' => $model->applicant_user_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'applicant_user_id' => $model->applicant_user_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'applicant_user_id',
            'surname',
            'other_name',
            'email_address:email',
            'country_code',
            'mobile_no',
            'password',
            'activation_code',
            'salt',
            'status',
            'date_registered',
            'reg_token',
            'profile_image',
            'first_name',
            'change_pass',
            'username',
        ],
    ]) ?>

</div>
