<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_contact_types".
 *
 * @property int $contact_type_id
 * @property string|null $contact_type_name
 */
class AppContactTypes extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_contact_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contact_type_name'], 'default', 'value' => null],
            [['contact_type_name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'contact_type_id' => 'Contact Type ID',
            'contact_type_name' => 'Contact Type Name',
        ];
    }

}
