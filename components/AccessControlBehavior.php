<?php

namespace app\components;

use Yii;
use yii\base\Behavior;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;

class AccessControlBehavior extends Behavior
{
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction($event)
    {
        $actionId = $event->action->id;
        // Allow access to login and error actions unconditionally
        if (in_array($actionId, ['login', 'error'])) {
            return true;
        }

        if (Yii::$app->user->isGuest) {
            // Allow access to specific public actions in SiteController
            if (Yii::$app->controller->id === 'site' && in_array($actionId, ['index', 'about', 'contact', 'captcha'])) {
                 return true;
            }
            Yii::$app->user->loginRequired();
            $event->isValid = false; // Stop further action execution
            return false;
        }

        return true;
    }
}
