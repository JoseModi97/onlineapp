<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "onlineapp.app_transaction_logs".
 *
 * @property int $transaction_log_id
 * @property string $application_ref_no
 * @property string|null $transaction_ref
 * @property string|null $payment_date
 * @property string|null $receipt_no
 * @property string|null $description
 * @property string|null $amount_paid
 * @property string|null $channel
 * @property string|null $currency
 * @property string|null $secondary_description
 * @property string|null $status
 * @property string|null $date_added
 * @property int|null $is_reconciliation
 * @property string|null $merchant_receipt
 * @property string|null $card_type
 * @property string|null $order_info
 * @property string|null $bank_reference
 */
class AppTransactionLogs extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'onlineapp.app_transaction_logs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['transaction_ref', 'payment_date', 'receipt_no', 'description', 'amount_paid', 'channel', 'secondary_description', 'status', 'date_added', 'merchant_receipt', 'card_type', 'order_info', 'bank_reference'], 'default', 'value' => null],
            [['currency'], 'default', 'value' => 'KES'],
            [['is_reconciliation'], 'default', 'value' => 0],
            [['application_ref_no'], 'required'],
            [['description', 'secondary_description'], 'string'],
            [['date_added'], 'safe'],
            [['is_reconciliation'], 'default', 'value' => null],
            [['is_reconciliation'], 'integer'],
            [['application_ref_no', 'transaction_ref', 'amount_paid', 'channel', 'currency', 'status', 'merchant_receipt', 'order_info', 'bank_reference'], 'string', 'max' => 255],
            [['payment_date'], 'string', 'max' => 30],
            [['receipt_no'], 'string', 'max' => 20],
            [['card_type'], 'string', 'max' => 11],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'transaction_log_id' => 'Transaction Log ID',
            'application_ref_no' => 'Application Ref No',
            'transaction_ref' => 'Transaction Ref',
            'payment_date' => 'Payment Date',
            'receipt_no' => 'Receipt No',
            'description' => 'Description',
            'amount_paid' => 'Amount Paid',
            'channel' => 'Channel',
            'currency' => 'Currency',
            'secondary_description' => 'Secondary Description',
            'status' => 'Status',
            'date_added' => 'Date Added',
            'is_reconciliation' => 'Is Reconciliation',
            'merchant_receipt' => 'Merchant Receipt',
            'card_type' => 'Card Type',
            'order_info' => 'Order Info',
            'bank_reference' => 'Bank Reference',
        ];
    }

}
