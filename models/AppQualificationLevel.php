<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_qualification_level".
 *
 * @property int $qualification_level_id
 * @property string|null $qualification_level_name
 */
class AppQualificationLevel extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_qualification_level';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['qualification_level_name'], 'default', 'value' => null],
            [['qualification_level_name'], 'string', 'max' => 150],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'qualification_level_id' => 'Qualification Level ID',
            'qualification_level_name' => 'Qualification Level Name',
        ];
    }

}
