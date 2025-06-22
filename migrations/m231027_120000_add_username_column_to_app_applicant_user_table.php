<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%app_applicant_user}}`.
 */
class m231027_120000_add_username_column_to_app_applicant_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%onlineapp.app_applicant_user}}', 'username', $this->string(255)->unique()->after('change_pass'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%onlineapp.app_applicant_user}}', 'username');
    }
}
