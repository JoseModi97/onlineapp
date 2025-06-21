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
                        <div class="col-lg-6 d-none d-lg-block bg-login-image" style="background: url('<?= Url::to('@web/auth_bundle/img/login-image.jpg') ?>'); background-position: center; background-size: cover;"></div>
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
                                     'inputOptions' => ['class' => 'custom-control-input']
                                ])->checkbox() ?>

                                <?= Html::submitButton('Login', ['class' => 'btn btn-primary btn-user btn-block', 'name' => 'login-button']) ?>

                                <hr>
                                <!-- Social login buttons - keep as links for now, functionality is separate -->
                                <a href="#" class="btn btn-google btn-user btn-block">
                                    <i class="fab fa-google fa-fw"></i> Login with Google
                                </a>
                                <a href="#" class="btn btn-facebook btn-user btn-block">
                                    <i class="fab fa-facebook-f fa-fw"></i> Login with Facebook
                                </a>
                                <?php ActiveForm::end(); ?>
                                <hr>
                                <div class="text-center">
                                    <a class="small" href="<?= Url::to(['site/request-password-reset']) ?>">Forgot Password?</a>
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
<!-- Note: The original HTML had JS includes at the end of the body. -->
<!-- These are now expected to be handled by the layout file `views/layouts/auth.php` -->
<!-- and Yii's asset manager. -->
