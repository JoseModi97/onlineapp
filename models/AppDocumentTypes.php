<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_document_types".
 *
 * @property int $doc_type_id
 * @property string|null $doc_type_name
 */
class AppDocumentTypes extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_document_types';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['doc_type_name'], 'default', 'value' => null],
            [['doc_type_name'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'doc_type_id' => 'Doc Type ID',
            'doc_type_name' => 'Doc Type Name',
        ];
    }

}
