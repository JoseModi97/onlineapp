<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_applicant_user".
 *
 * @property int $applicant_user_id
 * @property string|null $surname
 * @property string|null $other_name
 * @property string|null $email_address
 * @property string|null $country_code
 * @property string|null $mobile_no
 * @property string|null $password
 * @property string|null $activation_code
 * @property string|null $salt
 * @property string|null $status
 * @property string|null $date_registered
 * @property string|null $reg_token
 * @property string|null $profile_image
 * @property string $first_name
 * @property string|null $change_pass
 */
class AppApplicantUser extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_applicant_user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['surname', 'other_name', 'email_address', 'country_code', 'mobile_no', 'password', 'activation_code', 'salt', 'status', 'date_registered', 'reg_token', 'profile_image', 'change_pass'], 'default', 'value' => null],
            [['date_registered'], 'safe'],
            [['first_name'], 'required'],
            [['change_pass'], 'string'],
            [['surname', 'email_address', 'activation_code', 'salt', 'reg_token'], 'string', 'max' => 100],
            [['other_name'], 'string', 'max' => 150],
            [['country_code'], 'string', 'max' => 15],
            [['mobile_no', 'status'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 200],
            [['profile_image', 'first_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'applicant_user_id' => 'Applicant User ID',
            'surname' => 'Surname',
            'other_name' => 'Other Name',
            'email_address' => 'Email Address',
            'country_code' => 'Country Code',
            'mobile_no' => 'Mobile No',
            'password' => 'Password',
            'activation_code' => 'Activation Code',
            'salt' => 'Salt',
            'status' => 'Status',
            'date_registered' => 'Date Registered',
            'reg_token' => 'Reg Token',
            'profile_image' => 'Profile Image',
            'first_name' => 'First Name',
            'change_pass' => 'Change Pass',
        ];
    }

}
