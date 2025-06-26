<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_institution_types".
 *
 * @property int $institution_type_id
 * @property string|null $institution_type_name
 */
class AppInstitutionTypes extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_institution_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['institution_type_name'], 'default', 'value' => null],
            [['institution_type_id'], 'required'],
            [['institution_type_id'], 'default', 'value' => null],
            [['institution_type_id'], 'integer'],
            [['institution_type_name'], 'string', 'max' => 150],
            [['institution_type_id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'institution_type_id' => 'Institution Type ID',
            'institution_type_name' => 'Institution Type Name',
        ];
    }

}
