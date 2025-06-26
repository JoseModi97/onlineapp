<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_institutions".
 *
 * @property int $institution_code
 * @property string|null $institution_name
 * @property int|null $country_code
 * @property string|null $recognized
 * @property int|null $institution_type_id
 */
class AppInstitutions extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_institutions';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['institution_name', 'country_code', 'recognized', 'institution_type_id'], 'default', 'value' => null],
            [['country_code', 'institution_type_id'], 'default', 'value' => null],
            [['country_code', 'institution_type_id'], 'integer'],
            [['institution_name'], 'string', 'max' => 100],
            [['recognized'], 'string', 'max' => 8],
            [['institution_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppInstitutionTypes::class, 'targetAttribute' => ['institution_type_id' => 'institution_type_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'institution_code' => 'Institution Code',
            'institution_name' => 'Institution Name',
            'country_code' => 'Country Code',
            'recognized' => 'Recognized',
            'institution_type_id' => 'Institution Type ID',
        ];
    }
}
