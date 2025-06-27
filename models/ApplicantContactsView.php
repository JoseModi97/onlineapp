<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.applicant_contacts_view".
 *
 * @property string $application_ref_no
 * @property int $contact_id
 * @property string|null $full_names
 * @property int $contact_type_id
 * @property string|null $postal_address
 * @property string|null $postal_code
 * @property string|null $town
 * @property string|null $mobile_no
 * @property string|null $email_address
 * @property int|null $country_code
 * @property string|null $relationship
 */
class ApplicantContactsView extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.applicant_contacts_view';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['full_names', 'postal_address', 'postal_code', 'town', 'mobile_no', 'email_address', 'country_code', 'relationship'], 'default', 'value' => null],
            [['application_ref_no', 'contact_id', 'contact_type_id'], 'required'],
            [['contact_id', 'contact_type_id', 'country_code'], 'default', 'value' => null],
            [['contact_id', 'contact_type_id', 'country_code'], 'integer'],
            [['application_ref_no', 'full_names', 'postal_address', 'postal_code', 'town', 'email_address', 'relationship'], 'string', 'max' => 50],
            [['mobile_no'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'application_ref_no' => 'Application Ref No',
            'contact_id' => 'Contact ID',
            'full_names' => 'Full Names',
            'contact_type_id' => 'Contact Type ID',
            'postal_address' => 'Postal Address',
            'postal_code' => 'Postal Code',
            'town' => 'Town',
            'mobile_no' => 'Mobile No',
            'email_address' => 'Email Address',
            'country_code' => 'Country Code',
            'relationship' => 'Relationship',
        ];
    }

}
