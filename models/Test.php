<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.test".
 *
 * @property int $table-id
 */
class Test extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.test';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['table-id'], 'required'],
            [['table-id'], 'default', 'value' => null],
            [['table-id'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'table-id' => 'Table ID',
        ];
    }

}
