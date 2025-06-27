<?php

namespace app\models;

use Yii;
use app\models\AppApplicant; // Added
use app\models\AppContactTypes; // Added

/**
 * This is the model class for table "onlineapp.app_applicant_contacts".
 *
 * @property int $contact_id
 * @property int $applicant_id
 * @property int $contact_type_id
 * @property string|null $full_names
 * @property string|null $calling_code
 * @property string|null $mobile_no
 * @property string|null $email_address
 * @property string|null $postal_address
 * @property string|null $postal_code
 * @property string|null $town
 * @property int|null $country_code
 * @property int|null $primary_contact
 * @property string|null $relationship
 */
class AppApplicantContacts extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_applicant_contacts';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['full_names', 'calling_code', 'mobile_no', 'email_address', 'postal_address', 'postal_code', 'town', 'country_code', 'primary_contact', 'relationship'], 'default', 'value' => null],
            [['contact_id', 'applicant_id', 'contact_type_id'], 'required'],
            [['contact_id', 'applicant_id', 'contact_type_id', 'country_code', 'primary_contact'], 'default', 'value' => null],
            [['contact_id', 'applicant_id', 'contact_type_id', 'country_code', 'primary_contact'], 'integer'],
            [['full_names', 'email_address', 'postal_address', 'postal_code', 'town', 'relationship'], 'string', 'max' => 50],
            [['calling_code'], 'string', 'max' => 10],
            [['mobile_no'], 'string', 'max' => 20],
            [['contact_id'], 'unique'],
            [['applicant_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppApplicant::class, 'targetAttribute' => ['applicant_id' => 'applicant_id']],
            [['contact_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppContactTypes::class, 'targetAttribute' => ['contact_type_id' => 'contact_type_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'contact_id' => 'Contact ID',
            'applicant_id' => 'Applicant ID',
            'contact_type_id' => 'Contact Type ID',
            'full_names' => 'Full Names',
            'calling_code' => 'Calling Code',
            'mobile_no' => 'Mobile No',
            'email_address' => 'Email Address',
            'postal_address' => 'Postal Address',
            'postal_code' => 'Postal Code',
            'town' => 'Town',
            'country_code' => 'Country Code',
            'primary_contact' => 'Primary Contact',
            'relationship' => 'Relationship',
        ];
    }

    /**
     * Gets query for [[Applicant]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getApplicant()
    {
        return $this->hasOne(AppApplicant::class, ['applicant_id' => 'applicant_id']);
    }

    /**
     * Gets query for [[ContactType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getContactType()
    {
        return $this->hasOne(AppContactTypes::class, ['contact_type_id' => 'contact_type_id']);
    }
}
