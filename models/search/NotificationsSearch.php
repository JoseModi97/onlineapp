<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppNotifications;

/**
 * NotificationsSearch represents the model behind the search form of `app\models\AppNotifications`.
 */
class NotificationsSearch extends AppNotifications
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['notification_id', 'applicant_id', 'application_ref_no', 'sent_status', 'message_read', 'user_deleted'], 'integer'],
            [['notification_type', 'recipient', 'sender', 'subject', 'message', 'date_added', 'date_sent'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = AppNotifications::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'notification_id' => $this->notification_id,
            'applicant_id' => $this->applicant_id,
            'application_ref_no' => $this->application_ref_no,
            'date_added' => $this->date_added,
            'date_sent' => $this->date_sent,
            'sent_status' => $this->sent_status,
            'message_read' => $this->message_read,
            'user_deleted' => $this->user_deleted,
        ]);

        $query->andFilterWhere(['ilike', 'notification_type', $this->notification_type])
            ->andFilterWhere(['ilike', 'recipient', $this->recipient])
            ->andFilterWhere(['ilike', 'sender', $this->sender])
            ->andFilterWhere(['ilike', 'subject', $this->subject])
            ->andFilterWhere(['ilike', 'message', $this->message]);

        return $dataProvider;
    }
}
