<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_generalparams".
 *
 * @property int $param_id
 * @property string|null $param_name
 * @property string|null $param_value
 */
class AppGeneralparams extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_generalparams';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['param_name', 'param_value'], 'default', 'value' => null],
            [['param_name'], 'string', 'max' => 30],
            [['param_value'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'param_id' => 'Param ID',
            'param_name' => 'Param Name',
            'param_value' => 'Param Value',
        ];
    }

}
