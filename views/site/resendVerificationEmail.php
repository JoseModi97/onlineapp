<?php

/** @var yii\web\View $this */
/** @var yii\bootstrap5\ActiveForm $form */
/** @var \app\models\ResendVerificationEmailForm $model */ // Corrected model type in docblock

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; // Keeping bootstrap5 ActiveForm
use yii\helpers\Url;

$this->title = 'Resend Verification Email';
// $this->params['breadcrumbs'][] = $this->title; // Breadcrumbs not typical for this layout

// The layout file (views/layouts/auth.php) is expected to include necessary assets.
// We'll use a structure similar to forgot-password page, with bg-password-image or a generic one.
// For variety or if a specific "resend" image existed, we could use it. Sticking to bg-password-image for now.
?>
<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-password-image"></div> <!-- Or a more generic image if available -->
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-2"><?= Html::encode($this->title) ?></h1>
                                    <p class="mb-4">Please enter your email address below. If an account exists, we'll send a new verification link.</p>
                                </div>
                                <?php $form = ActiveForm::begin([
                                    'id' => 'resend-verification-email-form',
                                    'options' => ['class' => 'user'],
                                    'fieldConfig' => [
                                        'template' => "{input}\n{hint}\n{error}",
                                        'inputOptions' => ['class' => 'form-control form-control-user'],
                                    ],
                                ]); ?>

                                    <?= $form->field($model, 'email', [
                                        'inputOptions' => [
                                            'class' => 'form-control form-control-user',
                                            'placeholder' => 'Enter Email Address...',
                                            'autofocus' => true
                                        ]
                                    ])->label(false) ?>

                                    <?= Html::submitButton('Resend Verification Email', ['class' => 'btn btn-primary btn-user btn-block']) ?>

                                <?php ActiveForm::end(); ?>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?= Url::to(['site/signup']) ?>">Create an Account!</a>
                                </div>
                                <div class="text-center">
                                    <a class="small" href="<?= Url::to(['site/login']) ?>">Already have an account? Login!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>