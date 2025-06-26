<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_education_system".
 *
 * @property int $edu_system_code
 * @property string|null $edu_system_name
 * @property string|null $duration
 * @property string|null $examining_body
 * @property string|null $education_category
 */
class AppEducationSystem extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_education_system';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['edu_system_name', 'duration', 'examining_body', 'education_category'], 'default', 'value' => null],
            [['edu_system_name', 'examining_body', 'education_category'], 'string', 'max' => 100],
            [['duration'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'edu_system_code' => 'Edu System Code',
            'edu_system_name' => 'Edu System Name',
            'duration' => 'Duration',
            'examining_body' => 'Examining Body',
            'education_category' => 'Education Category',
        ];
    }

}
