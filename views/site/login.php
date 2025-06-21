<?php

/** @var yii\web\View $this */
/** @var app\models\LoginForm $model */

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm; // Assuming Bootstrap 5 based on previous files
use yii\helpers\Url;

// The $this->title is usually set in the controller or before rendering this view.
// We'll keep the static title from the HTML for now, but it can be dynamic.
// $this->title = 'Login';

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
                        <div class="col-lg-6 d-none d-lg-block bg-login-image" style="background: url('<?= Url::to('@web/img/9175328_6531.svg') ?>'); background-position: center; background-size: cover;"></div>
                        <div class="col-lg-6">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 text-gray-900 mb-4">Welcome Back!</h1>
                                </div>

                                <?php $form = ActiveForm::begin([
                                    'id' => 'login-form',
                                    'options' => ['class' => 'user'],
                                    'fieldConfig' => [
                                        // To match the theme, we might need to customize templates or use specific CSS classes.
                                        // For now, let's use default Yii ActiveForm rendering and adjust if needed.
                                        'inputOptions' => ['class' => 'form-control form-control-user'],
                                        'template' => "{input}\n{error}", // Basic template, remove label
                                    ],
                                ]); ?>

                                <?= $form->field($model, 'username', [
                                    'inputOptions' => [
                                        'class' => 'form-control form-control-user',
                                        'placeholder' => 'Enter Email Address...' // Or Username, depending on your LoginForm model
                                    ],
                                ])->textInput(['autofocus' => true]) ?>

                                <?= $form->field($model, 'password', [
                                    'inputOptions' => [
                                        'class' => 'form-control form-control-user',
                                        'placeholder' => 'Password'
                                    ],
                                ])->passwordInput() ?>

                                <?= $form->field($model, 'rememberMe', [
                                    'template' => "<div class=\"form-group\"><div class=\"custom-control custom-checkbox small\">{input} {label}</div></div>\n{error}",
                                    'labelOptions' => ['class' => 'custom-control-label'],
                                    'inputOptions' => ['class' => 'custom-control-input'],
                                    // Template might need adjustment if default checkbox styling of SB Admin is very different
                                ])->checkbox() ?>

                                <div class="my-1 mx-0" style="color:#999; font-size: 0.8rem; margin-bottom: 1rem; margin-top: 1rem;">
                                    Do you have an Account? <?= Html::a('Sign Up', ['site/site/signup']) ?>.
                                    <br>
                                    If you forgot your password you can <?= Html::a('reset it', ['site/request-password-reset']) ?>.
                                    <br>
                                    Need new verification email? <?= Html::a('Resend', ['site/resend-verification-email']) ?>
                                </div>

                                <div class="form-group">
                                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-user btn-block', 'name' => 'login-button']) ?>
                                </div>

                                <?php ActiveForm::end(); ?>
                                <!-- Removed social login, forgot password, and create account links from here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>