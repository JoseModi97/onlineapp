<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_postgrad_fee_structure".
 *
 * @property int $fee_structure_id
 * @property string $degree_code
 * @property string|null $programme_name
 * @property string|null $fee_structure_body Html code for the fee structire minus teh javascript
 * @property string|null $additional_notes Notes regarding applicaiton, fee payment and any other important stuff
 * @property string|null $salutation Salutation at the end of the letter if any
 * @property string|null $date_added
 * @property string|null $date_modified Automatically updates on current timestamp
 */
class AppPostgradFeeStructure extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_postgrad_fee_structure';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['programme_name', 'fee_structure_body', 'additional_notes', 'salutation', 'date_added', 'date_modified'], 'default', 'value' => null],
            [['degree_code'], 'required'],
            [['fee_structure_body', 'additional_notes'], 'string'],
            [['date_added', 'date_modified'], 'safe'],
            [['degree_code'], 'string', 'max' => 10],
            [['programme_name'], 'string', 'max' => 255],
            [['salutation'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'fee_structure_id' => 'Fee Structure ID',
            'degree_code' => 'Degree Code',
            'programme_name' => 'Programme Name',
            'fee_structure_body' => 'Fee Structure Body',
            'additional_notes' => 'Additional Notes',
            'salutation' => 'Salutation',
            'date_added' => 'Date Added',
            'date_modified' => 'Date Modified',
        ];
    }

}
