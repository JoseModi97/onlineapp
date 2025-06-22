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
 * @property string|null $username
 */
class AppApplicantUser extends \yii\db\ActiveRecord
{
    const SCENARIO_DEFAULT = 'default';
    // Define scenario for personal details step
    const SCENARIO_STEP_PERSONAL_DETAILS = 'step_personal_details';
    // Define scenario for account settings step
    const SCENARIO_STEP_ACCOUNT_SETTINGS = 'step_account_settings';


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_applicant_user';
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_STEP_PERSONAL_DETAILS] = ['surname', 'first_name', 'other_name', 'email_address', 'mobile_no'];
        $scenarios[self::SCENARIO_STEP_ACCOUNT_SETTINGS] = ['username', 'password', 'profile_image', 'change_pass'];
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['surname', 'other_name', 'email_address', 'mobile_no', 'password', 'activation_code', 'salt', 'status', 'date_registered', 'reg_token', 'profile_image', 'change_pass', 'username', 'first_name'], 'default', 'value' => null],
            [['date_registered'], 'safe'],
            // [['first_name'], 'required'], // Username will be set from User model, so not required here initially. Made optional as per user request.
            [['change_pass'], 'string'],
            [['surname', 'email_address', 'activation_code', 'salt', 'reg_token'], 'string', 'max' => 100],
            [['other_name'], 'string', 'max' => 150],
            // [['country_code'], 'string', 'max' => 15], // Property removed, was for AppApplicant
            [['mobile_no', 'status'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 200],
            [['profile_image', 'first_name', 'username'], 'string', 'max' => 255],
            [['username'], 'unique'],
            [['applicant_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_user_id' => 'applicant_user_id']],

            // Scenario specific rules
            [['surname', 'first_name', 'email_address', 'mobile_no'], 'required', 'on' => self::SCENARIO_STEP_PERSONAL_DETAILS],
            [['username'], 'required', 'on' => self::SCENARIO_STEP_ACCOUNT_SETTINGS],
            // Password required only if it's a new record or if change_pass is checked
            ['password', 'required', 'on' => self::SCENARIO_STEP_ACCOUNT_SETTINGS, 'when' => function ($model) {
                return $model->isNewRecord || $model->change_pass;
            }, 'whenClient' => "function (attribute, value) {
                return $('#appapplicantuser-change_pass').is(':checked') || " . ($this->isNewRecord ? 'true' : 'false') . ";
            }"],
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
            // 'country_code' => 'Country Code', // Removed as this property no longer exists on this model
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
            'username' => 'Username',
        ];
    }

    /**
     * Gets query for [[ApplicationFee]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAppApplicant()
    {
        return $this->hasOne(AppApplicant::class, ['applicant_user_id' => 'applicant_user_id']);
    }
}
