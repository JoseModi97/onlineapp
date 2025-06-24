<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicant;

/**
 * ApplicantSearch represents the model behind the search form of `app\models\AppApplicant`.
 */
class ApplicantSearch extends AppApplicant
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applicant_id', 'applicant_user_id', 'country_code', 'national_id'], 'integer'],
            [['gender', 'dob', 'religion', 'marital_status'], 'safe'],
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
        $query = AppApplicant::find();

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
            'applicant_id' => $this->applicant_id,
            'applicant_user_id' => $this->applicant_user_id,
            'dob' => $this->dob,
            'country_code' => $this->country_code,
            'national_id' => $this->national_id,
        ]);

        $query->andFilterWhere(['ilike', 'gender', $this->gender])
            ->andFilterWhere(['ilike', 'religion', $this->religion])
            ->andFilterWhere(['ilike', 'marital_status', $this->marital_status]);

        return $dataProvider;
    }
}
