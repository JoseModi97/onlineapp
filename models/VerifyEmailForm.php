<?php

namespace app\models;

use app\models\User;
use app\models\AppApplicantUser;
use yii\base\InvalidArgumentException;
use yii\base\Model;

class VerifyEmailForm extends Model
{
    /**
     * @var string
     */
    public $token;

    /**
     * @var User
     */
    private $_user;


    /**
     * Creates a form model with given token.
     *
     * @param string $token
     * @param array $config name-value pairs that will be used to initialize the object properties
     * @throws InvalidArgumentException if token is empty or not valid
     */
    public function __construct($token, array $config = [])
    {
        if (empty($token) || !is_string($token)) {
            throw new InvalidArgumentException('Verify email token cannot be blank.');
        }
        $this->_user = User::findByVerificationToken($token);
        if (!$this->_user) {
            throw new InvalidArgumentException('Wrong verify email token.');
        }
        parent::__construct($config);
    }

    /**
     * Verify email
     *
     * @return User|null the saved model or null if saving fails
     */
    public function verifyEmail()
    {
        $user = $this->_user;
        $user->status = User::STATUS_ACTIVE;

        if ($user->save(false)) {
            // Create a new AppApplicantUser record
            $appApplicantUser = new AppApplicantUser();
            $appApplicantUser->username = $user->username; // Use the new username field
            $appApplicantUser->email_address = $user->email; // Populate email address
            // first_name is no longer required and will not be set here.
            if (!$appApplicantUser->save()) {
                // Handle error if AppApplicantUser fails to save
                // For now, we can log this or add a flash message
                // Depending on how critical this step is, you might also want to
                // roll back the user status change or notify an admin.
                \Yii::error("Failed to save AppApplicantUser: " . print_r($appApplicantUser->getErrors(), true));
                // Optionally, you could decide not to return the user if this part fails
                // return null;
            }
            return $user;
        }
        return null;
    }
}
