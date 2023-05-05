<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230612180137 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE answer_variant_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bot_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bot_access_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE bot_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE jump_condition_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE question_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE respondent_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE respondent_answer_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE respondent_form_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE schedule_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE social_network_config_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE subcondition_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE survey_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE survey_access_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE survey_iteration_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE survey_user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE user_data_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE answer_variant (id INT NOT NULL, question_id INT NOT NULL, value VARCHAR(255) NOT NULL, serial_number SMALLINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B90370DC1E27F6BF ON answer_variant (question_id)');
        $this->addSql('CREATE TABLE bot (id INT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, is_private BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_11F04112B36786B ON bot (title)');
        $this->addSql('CREATE TABLE bot_access (id INT NOT NULL, respondent_id INT DEFAULT NULL, bot_id INT NOT NULL, property_name VARCHAR(32) DEFAULT NULL, property_value VARCHAR(128) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FF6E7E8DCE80CD19 ON bot_access (respondent_id)');
        $this->addSql('CREATE INDEX IDX_FF6E7E8D92C1C487 ON bot_access (bot_id)');
        $this->addSql('CREATE TABLE bot_user (id INT NOT NULL, bot_id INT NOT NULL, user_data_id INT NOT NULL, role VARCHAR(32) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C355A3B92C1C487 ON bot_user (bot_id)');
        $this->addSql('CREATE INDEX IDX_C355A3B6FF8BF36 ON bot_user (user_data_id)');
        $this->addSql('CREATE TABLE jump_condition (id INT NOT NULL, survey_id INT NOT NULL, to_question_id INT NOT NULL, serial_number SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_73959DDFB3FE509D ON jump_condition (survey_id)');
        $this->addSql('CREATE INDEX IDX_73959DDF30B4C8DC ON jump_condition (to_question_id)');
        $this->addSql('CREATE TABLE question (id INT NOT NULL, survey_id INT NOT NULL, type VARCHAR(32) NOT NULL, serial_number SMALLINT NOT NULL, title VARCHAR(400) NOT NULL, answer_value_type VARCHAR(32) DEFAULT \'string\' NOT NULL, interval_borders VARCHAR(255) DEFAULT NULL, is_required BOOLEAN DEFAULT true NOT NULL, own_answers_count SMALLINT DEFAULT 0 NOT NULL, max_variants SMALLINT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B6F7494EB3FE509D ON question (survey_id)');
        $this->addSql('CREATE TABLE respondent (id INT NOT NULL, telegram_id BIGINT DEFAULT NULL, vkontakte_id BIGINT DEFAULT NULL, email VARCHAR(128) DEFAULT NULL, phone VARCHAR(16) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_409B5150CC0B3066 ON respondent (telegram_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_409B515089588C72 ON respondent (vkontakte_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_409B5150E7927C74 ON respondent (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_409B5150444F97DD ON respondent (phone)');
        $this->addSql('CREATE TABLE respondent_answer (id INT NOT NULL, respondent_id INT NOT NULL, question_id INT NOT NULL, answer_variant_id INT DEFAULT NULL, form_id INT NOT NULL, value VARCHAR(1024) DEFAULT NULL, serial_number SMALLINT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_FA2BD17FCE80CD19 ON respondent_answer (respondent_id)');
        $this->addSql('CREATE INDEX IDX_FA2BD17F1E27F6BF ON respondent_answer (question_id)');
        $this->addSql('CREATE INDEX IDX_FA2BD17F54E42191 ON respondent_answer (answer_variant_id)');
        $this->addSql('CREATE INDEX IDX_FA2BD17F5FF69B7D ON respondent_answer (form_id)');
        $this->addSql('CREATE TABLE respondent_form (id INT NOT NULL, survey_id INT NOT NULL, respondent_id INT NOT NULL, sent_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4530324CB3FE509D ON respondent_form (survey_id)');
        $this->addSql('CREATE INDEX IDX_4530324CCE80CD19 ON respondent_form (respondent_id)');
        $this->addSql('CREATE TABLE schedule (id INT NOT NULL, survey_id INT NOT NULL, type VARCHAR(32) NOT NULL, repeat_values JSON NOT NULL, next_repeat TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, is_once BOOLEAN DEFAULT true NOT NULL, is_notice_on_start BOOLEAN DEFAULT false NOT NULL, notice_before INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A3811FBB3FE509D ON schedule (survey_id)');
        $this->addSql('CREATE TABLE social_network_config (id INT NOT NULL, bot_id INT NOT NULL, code VARCHAR(32) NOT NULL, connection_id VARCHAR(32) NOT NULL, is_enabled BOOLEAN DEFAULT false NOT NULL, is_active BOOLEAN DEFAULT false NOT NULL, access_token VARCHAR(255) NOT NULL, webhook_url_path VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_414DC5C992C1C487 ON social_network_config (bot_id)');
        $this->addSql('CREATE TABLE subcondition (id INT NOT NULL, jump_condition_id INT NOT NULL, answer_variant_id INT NOT NULL, serial_number SMALLINT NOT NULL, is_equal BOOLEAN DEFAULT true NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_29B9237AA41E24D2 ON subcondition (jump_condition_id)');
        $this->addSql('CREATE INDEX IDX_29B9237A54E42191 ON subcondition (answer_variant_id)');
        $this->addSql('CREATE TABLE survey (id INT NOT NULL, bot_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, is_private BOOLEAN DEFAULT true NOT NULL, is_enabled BOOLEAN DEFAULT false NOT NULL, is_multiple BOOLEAN DEFAULT false NOT NULL, is_phone_required BOOLEAN DEFAULT false NOT NULL, is_email_required BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_AD5F9BFC92C1C487 ON survey (bot_id)');
        $this->addSql('CREATE TABLE survey_access (id INT NOT NULL, respondent_id INT DEFAULT NULL, survey_id INT NOT NULL, property_name VARCHAR(32) NOT NULL, property_value VARCHAR(128) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2E67E338CE80CD19 ON survey_access (respondent_id)');
        $this->addSql('CREATE INDEX IDX_2E67E338B3FE509D ON survey_access (survey_id)');
        $this->addSql('CREATE TABLE survey_iteration (id INT NOT NULL, survey_id INT NOT NULL, start_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_survey_changed BOOLEAN DEFAULT false NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4B879330B3FE509D ON survey_iteration (survey_id)');
        $this->addSql('CREATE TABLE survey_user (id INT NOT NULL, survey_id INT NOT NULL, user_data_id INT DEFAULT NULL, role VARCHAR(32) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_4B7AD682B3FE509D ON survey_user (survey_id)');
        $this->addSql('CREATE INDEX IDX_4B7AD6826FF8BF36 ON survey_user (user_data_id)');
        $this->addSql('CREATE TABLE user_data (id INT NOT NULL, first_name VARCHAR(64) NOT NULL, last_name VARCHAR(64) NOT NULL, patronymic VARCHAR(64) DEFAULT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(16) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D772BFAAE7927C74 ON user_data (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D772BFAA444F97DD ON user_data (phone)');
        $this->addSql('CREATE TABLE cache_items (item_id VARCHAR(255) NOT NULL, item_data BYTEA NOT NULL, item_lifetime INT DEFAULT NULL, item_time INT NOT NULL, PRIMARY KEY(item_id))');
        $this->addSql('ALTER TABLE answer_variant ADD CONSTRAINT FK_B90370DC1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bot_access ADD CONSTRAINT FK_FF6E7E8DCE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bot_access ADD CONSTRAINT FK_FF6E7E8D92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bot_user ADD CONSTRAINT FK_C355A3B92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE bot_user ADD CONSTRAINT FK_C355A3B6FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE jump_condition ADD CONSTRAINT FK_73959DDFB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE jump_condition ADD CONSTRAINT FK_73959DDF30B4C8DC FOREIGN KEY (to_question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17FCE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17F1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17F54E42191 FOREIGN KEY (answer_variant_id) REFERENCES answer_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17F5FF69B7D FOREIGN KEY (form_id) REFERENCES respondent_form (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE respondent_form ADD CONSTRAINT FK_4530324CB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE respondent_form ADD CONSTRAINT FK_4530324CCE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE social_network_config ADD CONSTRAINT FK_414DC5C992C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subcondition ADD CONSTRAINT FK_29B9237AA41E24D2 FOREIGN KEY (jump_condition_id) REFERENCES jump_condition (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subcondition ADD CONSTRAINT FK_29B9237A54E42191 FOREIGN KEY (answer_variant_id) REFERENCES answer_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_access ADD CONSTRAINT FK_2E67E338CE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_access ADD CONSTRAINT FK_2E67E338B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_iteration ADD CONSTRAINT FK_4B879330B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD6826FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user_data (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE answer_variant_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bot_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bot_access_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE bot_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE jump_condition_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE question_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE respondent_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE respondent_answer_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE respondent_form_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE schedule_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE social_network_config_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE subcondition_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE survey_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE survey_access_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE survey_iteration_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE survey_user_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE user_data_id_seq CASCADE');
        $this->addSql('CREATE TABLE nma (id INT NOT NULL, a TEXT NOT NULL, b TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE answer_variant DROP CONSTRAINT FK_B90370DC1E27F6BF');
        $this->addSql('ALTER TABLE bot_access DROP CONSTRAINT FK_FF6E7E8DCE80CD19');
        $this->addSql('ALTER TABLE bot_access DROP CONSTRAINT FK_FF6E7E8D92C1C487');
        $this->addSql('ALTER TABLE bot_user DROP CONSTRAINT FK_C355A3B92C1C487');
        $this->addSql('ALTER TABLE bot_user DROP CONSTRAINT FK_C355A3B6FF8BF36');
        $this->addSql('ALTER TABLE jump_condition DROP CONSTRAINT FK_73959DDFB3FE509D');
        $this->addSql('ALTER TABLE jump_condition DROP CONSTRAINT FK_73959DDF30B4C8DC');
        $this->addSql('ALTER TABLE question DROP CONSTRAINT FK_B6F7494EB3FE509D');
        $this->addSql('ALTER TABLE respondent_answer DROP CONSTRAINT FK_FA2BD17FCE80CD19');
        $this->addSql('ALTER TABLE respondent_answer DROP CONSTRAINT FK_FA2BD17F1E27F6BF');
        $this->addSql('ALTER TABLE respondent_answer DROP CONSTRAINT FK_FA2BD17F54E42191');
        $this->addSql('ALTER TABLE respondent_answer DROP CONSTRAINT FK_FA2BD17F5FF69B7D');
        $this->addSql('ALTER TABLE respondent_form DROP CONSTRAINT FK_4530324CB3FE509D');
        $this->addSql('ALTER TABLE respondent_form DROP CONSTRAINT FK_4530324CCE80CD19');
        $this->addSql('ALTER TABLE schedule DROP CONSTRAINT FK_5A3811FBB3FE509D');
        $this->addSql('ALTER TABLE social_network_config DROP CONSTRAINT FK_414DC5C992C1C487');
        $this->addSql('ALTER TABLE subcondition DROP CONSTRAINT FK_29B9237AA41E24D2');
        $this->addSql('ALTER TABLE subcondition DROP CONSTRAINT FK_29B9237A54E42191');
        $this->addSql('ALTER TABLE survey DROP CONSTRAINT FK_AD5F9BFC92C1C487');
        $this->addSql('ALTER TABLE survey_access DROP CONSTRAINT FK_2E67E338CE80CD19');
        $this->addSql('ALTER TABLE survey_access DROP CONSTRAINT FK_2E67E338B3FE509D');
        $this->addSql('ALTER TABLE survey_iteration DROP CONSTRAINT FK_4B879330B3FE509D');
        $this->addSql('ALTER TABLE survey_user DROP CONSTRAINT FK_4B7AD682B3FE509D');
        $this->addSql('ALTER TABLE survey_user DROP CONSTRAINT FK_4B7AD6826FF8BF36');
        $this->addSql('DROP TABLE answer_variant');
        $this->addSql('DROP TABLE bot');
        $this->addSql('DROP TABLE bot_access');
        $this->addSql('DROP TABLE bot_user');
        $this->addSql('DROP TABLE jump_condition');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE respondent');
        $this->addSql('DROP TABLE respondent_answer');
        $this->addSql('DROP TABLE respondent_form');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE social_network_config');
        $this->addSql('DROP TABLE subcondition');
        $this->addSql('DROP TABLE survey');
        $this->addSql('DROP TABLE survey_access');
        $this->addSql('DROP TABLE survey_iteration');
        $this->addSql('DROP TABLE survey_user');
        $this->addSql('DROP TABLE user_data');
        $this->addSql('DROP TABLE cache_items');
    }
}
