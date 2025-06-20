<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicationTracking;

/**
 * ApplicationTrackingSearch represents the model behind the search form of `app\models\AppApplicationTracking`.
 */
class ApplicationTrackingSearch extends AppApplicationTracking
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['tracking_id', 'application_id', 'status_id'], 'integer'],
            [['remarks', 'audit_date', 'user_id'], 'safe'],
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
        $query = AppApplicationTracking::find();

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
            'tracking_id' => $this->tracking_id,
            'application_id' => $this->application_id,
            'status_id' => $this->status_id,
            'audit_date' => $this->audit_date,
        ]);

        $query->andFilterWhere(['ilike', 'remarks', $this->remarks])
            ->andFilterWhere(['ilike', 'user_id', $this->user_id]);

        return $dataProvider;
    }
}
