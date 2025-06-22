<?php

/** @var yii\web\View $this */
/** @var app\models\ResetPasswordForm $model */

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

$this->title = 'Reset Your Password';

// Asset registration is handled by the layout file views/layouts/auth.php
?>

<div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

        <div class="col-xl-10 col-lg-12 col-md-9">

            <div class="card o-hidden border-0 shadow-lg my-5">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-6 d-none d-lg-block bg-password-image" style="background: url('<?= Url::to('@web/img/reset-pw-page.svg') ?>'); background-position: center; background-size: cover;"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4"><?= Html::encode($this->title) ?></h1>
                                </div>
                                <p class="mb-4 text-center">Please choose your new password below.</p>

                                <?php $form = ActiveForm::begin([
                                    'id' => 'reset-password-form',
                                    'options' => ['class' => 'user'],
                                    'fieldConfig' => [
                                        'inputOptions' => ['class' => 'form-control form-control-user'],
                                        'template' => "{input}\n{error}", // Basic template, remove label
                                    ],
                                ]); ?>

                                <?= $form->field($model, 'password', [
                                    'inputOptions' => [
                                        'class' => 'form-control form-control-user',
                                        'placeholder' => 'Enter New Password'
                                    ],
                                ])->passwordInput(['autofocus' => true]) ?>

                                <div class="form-group mt-4">
                                    <?= Html::submitButton('Reset Password', ['class' => 'btn btn-primary btn-user btn-block', 'name' => 'reset-button']) ?>
                                </div>

                                <?php ActiveForm::end(); ?>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?= Url::to(['site/login']) ?>">Remembered your password? Login!</a>
                                </div>
                                <div class="text-center">
                                    <a class="small" href="<?= Url::to(['site/signup']) ?>">Create an Account!</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
