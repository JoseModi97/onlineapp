<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.degree_requirements".
 *
 * @property int $requirement_id
 * @property int $doc_type_id
 * @property string $degree_type
 * @property string $description
 */
class DegreeRequirements extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.degree_requirements';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['doc_type_id', 'degree_type', 'description'], 'required'],
            [['doc_type_id'], 'default', 'value' => null],
            [['doc_type_id'], 'integer'],
            [['description'], 'string'],
            [['degree_type'], 'string', 'max' => 50],
            [['doc_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => AppDocumentTypes::class, 'targetAttribute' => ['doc_type_id' => 'doc_type_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'requirement_id' => 'Requirement ID',
            'doc_type_id' => 'Doc Type ID',
            'degree_type' => 'Degree Type',
            'description' => 'Description',
        ];
    }
}
