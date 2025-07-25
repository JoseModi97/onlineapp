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
use yii\web\UploadedFile;

class AppApplicantUser extends \yii\db\ActiveRecord
{
    const SCENARIO_DEFAULT = 'default';
    // Define scenario for personal details step
    const SCENARIO_STEP_PERSONAL_DETAILS = 'step_personal_details';
    // Define scenario for account settings step
    const SCENARIO_STEP_ACCOUNT_SETTINGS = 'step_account_settings';

    /**
     * @var UploadedFile
     */
    public $profile_image_file;


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
        $scenarios[self::SCENARIO_STEP_PERSONAL_DETAILS] = [
            'surname', 'first_name', 'other_name', 'email_address', 'mobile_no', // Existing fields
            'username', 'profile_image', 'profile_image_file' // Added fields
        ];
        $scenarios[self::SCENARIO_STEP_ACCOUNT_SETTINGS] = [
            'password', 'change_pass' // Username and profile image removed
        ];
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
            [['change_pass'], 'string'], // Should ideally be boolean, but depends on form input
            [['surname', 'email_address', 'activation_code', 'salt', 'reg_token'], 'string', 'max' => 100],
            [['other_name'], 'string', 'max' => 150],
            [['mobile_no', 'status'], 'string', 'max' => 30],
            [['password'], 'string', 'max' => 200], // This might be for the new password input
            [['profile_image', 'first_name', 'username'], 'string', 'max' => 255],

            // General unique validation for username. Yii's unique validator should handle updates correctly by default.
            [['username'], 'unique'],

            [['applicant_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_user_id' => 'applicant_user_id']],

            // SCENARIO_STEP_PERSONAL_DETAILS rules
            [['surname', 'first_name', 'email_address', 'mobile_no', 'username'], 'required', 'on' => self::SCENARIO_STEP_PERSONAL_DETAILS],
            // Profile image file validation for Personal Details Step
            [['profile_image_file'], 'file',
                'skipOnEmpty' => true,
                'extensions' => 'png, jpg, jpeg',
                'maxSize' => 1024 * 1024 * 2, // 2MB Max
                'on' => self::SCENARIO_STEP_PERSONAL_DETAILS
            ],
            [['profile_image_file'], 'validateImageDimensions',
                'on' => self::SCENARIO_STEP_PERSONAL_DETAILS,
                'skipOnEmpty' => true
            ],

            // SCENARIO_STEP_ACCOUNT_SETTINGS rules
            // [['username'], 'required', 'on' => self::SCENARIO_STEP_ACCOUNT_SETTINGS], // Username requirement removed from this scenario
            ['password', 'required', 'on' => self::SCENARIO_STEP_ACCOUNT_SETTINGS, 'when' => function ($model) {
                return !empty($model->change_pass); // Password required only if change_pass is checked/true
            }, 'whenClient' => "function (attribute, value) {
                return $('#appapplicantuser-change_pass').is(':checked');
            }"],
            // Removed profile image validation from SCENARIO_STEP_ACCOUNT_SETTINGS as it's moved
        ];
    }

    /**
     * Validates the dimensions of the uploaded image.
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateImageDimensions($attribute, $params)
    {
        if ($this->$attribute instanceof UploadedFile) {
            $imageSize = getimagesize($this->$attribute->tempName);
            if ($imageSize === false && !$this->$attribute->hasError) {
                 // If getimagesize fails but Yii's UploadedFile doesn't report an error, it might not be a valid image format recognized by GD/Exif
                $this->addError($attribute, 'The uploaded file is not a valid image or the image type is not supported.');
                return;
            }

            // Additional check if getimagesize succeeded but might still be an issue
            if ($imageSize === false && $this->$attribute->hasError) {
                 // If UploadedFile already has an error (e.g. too large, not uploaded via HTTP POST), don't add another one here.
                 // The 'file' validator should have caught this.
                return;
            }


            if ($imageSize[0] != 100 || $imageSize[1] != 100) {
                $this->addError($attribute, 'Profile image must be 100x100 pixels. Uploaded image is ' . $imageSize[0] . 'x' . $imageSize[1] . ' pixels.');
            }
        } elseif (is_string($this->$attribute) && !empty($this->$attribute)) {
            // This case might occur if the attribute is already a string (e.g. filename from DB)
            // and not an UploadedFile instance. This validator is primarily for new uploads.
            // If needed, logic to validate existing file paths could be added here, but generally not required for this rule.
        }
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
            'mobile_no' => 'Mobile No',
            'password' => 'Password',
            'activation_code' => 'Activation Code',
            'salt' => 'Salt',
            'status' => 'Status',
            'date_registered' => 'Date Registered',
            'reg_token' => 'Reg Token',
            'profile_image' => 'Profile Image Filename', // Clarified label
            'profile_image_file' => 'Profile Image',    // Label for the file input
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
