<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\AppApplicantContacts $model */

$this->title = $model->contact_id;
$this->params['breadcrumbs'][] = ['label' => 'Applicant Contacts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="app-applicant-contacts-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'contact_id' => $model->contact_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'contact_id' => $model->contact_id], [
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
            'contact_id',
            'applicant_id',
            'contact_type_id',
            'full_names',
            'calling_code',
            'mobile_no',
            'email_address:email',
            'postal_address',
            'postal_code',
            'town',
            'country_code',
            'primary_contact',
            'relationship',
        ],
    ]) ?>

</div>