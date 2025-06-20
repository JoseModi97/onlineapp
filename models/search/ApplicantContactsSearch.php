<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicantContacts;

/**
 * ApplicantContactsSearch represents the model behind the search form of `app\models\AppApplicantContacts`.
 */
class ApplicantContactsSearch extends AppApplicantContacts
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['contact_id', 'applicant_id', 'contact_type_id', 'country_code', 'primary_contact'], 'integer'],
            [['full_names', 'calling_code', 'mobile_no', 'email_address', 'postal_address', 'postal_code', 'town', 'relationship'], 'safe'],
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
        $query = AppApplicantContacts::find();

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
            'contact_id' => $this->contact_id,
            'applicant_id' => $this->applicant_id,
            'contact_type_id' => $this->contact_type_id,
            'country_code' => $this->country_code,
            'primary_contact' => $this->primary_contact,
        ]);

        $query->andFilterWhere(['ilike', 'full_names', $this->full_names])
            ->andFilterWhere(['ilike', 'calling_code', $this->calling_code])
            ->andFilterWhere(['ilike', 'mobile_no', $this->mobile_no])
            ->andFilterWhere(['ilike', 'email_address', $this->email_address])
            ->andFilterWhere(['ilike', 'postal_address', $this->postal_address])
            ->andFilterWhere(['ilike', 'postal_code', $this->postal_code])
            ->andFilterWhere(['ilike', 'town', $this->town])
            ->andFilterWhere(['ilike', 'relationship', $this->relationship]);

        return $dataProvider;
    }
}
