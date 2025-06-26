<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicantWorkExp;

/**
 * ApplicantWorkExpSearch represents the model behind the search form of `app\models\AppApplicantWorkExp`.
 */
class ApplicantWorkExpSearch extends AppApplicantWorkExp
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['experience_id', 'applicant_id'], 'integer'],
            [['employer_name', 'designation', 'year_from', 'year_to', 'assignment', 'relevant'], 'safe'],
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
        $query = AppApplicantWorkExp::find();

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
            'experience_id' => $this->experience_id,
            'applicant_id' => $this->applicant_id,
            'year_from' => $this->year_from,
            'year_to' => $this->year_to,
        ]);

        $query->andFilterWhere(['ilike', 'employer_name', $this->employer_name])
            ->andFilterWhere(['ilike', 'designation', $this->designation])
            ->andFilterWhere(['ilike', 'assignment', $this->assignment])
            ->andFilterWhere(['ilike', 'relevant', $this->relevant]);

        return $dataProvider;
    }
}
