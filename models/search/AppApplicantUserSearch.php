<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\AppApplicantUser;

/**
 * AppApplicantUserSearch represents the model behind the search form of `app\models\AppApplicantUser`.
 */
class AppApplicantUserSearch extends AppApplicantUser
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['applicant_user_id'], 'integer'],
            [['surname', 'other_name', 'email_address', 'country_code', 'mobile_no', 'password', 'activation_code', 'salt', 'status', 'date_registered', 'reg_token', 'profile_image', 'first_name', 'change_pass', 'username'], 'safe'],
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
        $query = AppApplicantUser::find();

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
            'applicant_user_id' => $this->applicant_user_id,
            'date_registered' => $this->date_registered,
        ]);

        $query->andFilterWhere(['ilike', 'surname', $this->surname])
            ->andFilterWhere(['ilike', 'other_name', $this->other_name])
            ->andFilterWhere(['ilike', 'email_address', $this->email_address])
            ->andFilterWhere(['ilike', 'country_code', $this->country_code])
            ->andFilterWhere(['ilike', 'mobile_no', $this->mobile_no])
            ->andFilterWhere(['ilike', 'password', $this->password])
            ->andFilterWhere(['ilike', 'activation_code', $this->activation_code])
            ->andFilterWhere(['ilike', 'salt', $this->salt])
            ->andFilterWhere(['ilike', 'status', $this->status])
            ->andFilterWhere(['ilike', 'reg_token', $this->reg_token])
            ->andFilterWhere(['ilike', 'profile_image', $this->profile_image])
            ->andFilterWhere(['ilike', 'first_name', $this->first_name])
            ->andFilterWhere(['ilike', 'change_pass', $this->change_pass])
            ->andFilterWhere(['ilike', 'username', $this->username]);

        return $dataProvider;
    }
}
