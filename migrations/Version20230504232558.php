<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230504232558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer_variant (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, value VARCHAR(255) NOT NULL, serial_number SMALLINT DEFAULT NULL, INDEX IDX_B90370DC1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bot (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, is_private TINYINT(1) DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bot_access (id INT AUTO_INCREMENT NOT NULL, respondent_id INT DEFAULT NULL, bot_id INT NOT NULL, property_name VARCHAR(32) DEFAULT NULL, property_value VARCHAR(128) DEFAULT NULL, INDEX IDX_FF6E7E8DCE80CD19 (respondent_id), INDEX IDX_FF6E7E8D92C1C487 (bot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bot_user (id INT AUTO_INCREMENT NOT NULL, bot_id INT NOT NULL, user_data_id INT NOT NULL, role VARCHAR(32) NOT NULL, INDEX IDX_C355A3B92C1C487 (bot_id), INDEX IDX_C355A3B6FF8BF36 (user_data_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE jump_condition (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, to_question_id INT NOT NULL, serial_number SMALLINT NOT NULL, INDEX IDX_73959DDFB3FE509D (survey_id), INDEX IDX_73959DDF30B4C8DC (to_question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, type VARCHAR(32) NOT NULL, serial_number SMALLINT NOT NULL, title VARCHAR(400) NOT NULL, interval_borders VARCHAR(255) DEFAULT NULL, is_required TINYINT(1) DEFAULT 1 NOT NULL, own_answers_count SMALLINT DEFAULT 0 NOT NULL, max_variants SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_B6F7494E2B36786B (title), INDEX IDX_B6F7494EB3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE respondent (id INT AUTO_INCREMENT NOT NULL, telegram_id INT DEFAULT NULL, vkontakte_id INT DEFAULT NULL, email VARCHAR(128) DEFAULT NULL, phone VARCHAR(16) DEFAULT NULL, UNIQUE INDEX UNIQ_409B5150CC0B3066 (telegram_id), UNIQUE INDEX UNIQ_409B515089588C72 (vkontakte_id), UNIQUE INDEX UNIQ_409B5150E7927C74 (email), UNIQUE INDEX UNIQ_409B5150444F97DD (phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE respondent_answer (id INT AUTO_INCREMENT NOT NULL, respondent_id INT NOT NULL, question_id INT NOT NULL, answer_variant_id INT DEFAULT NULL, value VARCHAR(255) DEFAULT NULL, serial_number SMALLINT DEFAULT NULL, INDEX IDX_FA2BD17FCE80CD19 (respondent_id), INDEX IDX_FA2BD17F1E27F6BF (question_id), INDEX IDX_FA2BD17F54E42191 (answer_variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE respondent_form (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, respondent_id INT NOT NULL, sent_date DATETIME DEFAULT NULL, INDEX IDX_4530324CB3FE509D (survey_id), INDEX IDX_4530324CCE80CD19 (respondent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE schedule (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, type VARCHAR(32) NOT NULL, repeat_values JSON NOT NULL, next_repeat DATETIME DEFAULT NULL, is_once TINYINT(1) DEFAULT 1 NOT NULL, is_notice_on_start TINYINT(1) DEFAULT 0 NOT NULL, notice_before INT DEFAULT NULL, UNIQUE INDEX UNIQ_5A3811FBB3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_network_config (id INT AUTO_INCREMENT NOT NULL, bot_id INT NOT NULL, code VARCHAR(32) NOT NULL, connection_id VARCHAR(32) NOT NULL, access_token VARCHAR(255) NOT NULL, webhook_url_path VARCHAR(255) DEFAULT NULL, INDEX IDX_414DC5C992C1C487 (bot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE subcondition (id INT AUTO_INCREMENT NOT NULL, jump_condition_id INT NOT NULL, answer_variant_id INT NOT NULL, serial_number SMALLINT NOT NULL, is_equal TINYINT(1) DEFAULT 1 NOT NULL, INDEX IDX_29B9237AA41E24D2 (jump_condition_id), INDEX IDX_29B9237A54E42191 (answer_variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, is_private TINYINT(1) DEFAULT 1 NOT NULL, is_enabled TINYINT(1) DEFAULT 0 NOT NULL, is_multiple TINYINT(1) DEFAULT 0 NOT NULL, is_phone_required TINYINT(1) DEFAULT 0 NOT NULL, is_email_required TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_AD5F9BFC92C1C487 (bot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_access (id INT AUTO_INCREMENT NOT NULL, respondent_id INT DEFAULT NULL, survey_id INT NOT NULL, property_name VARCHAR(32) NOT NULL, property_value VARCHAR(128) NOT NULL, INDEX IDX_2E67E338CE80CD19 (respondent_id), INDEX IDX_2E67E338B3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_iteration (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, start_date DATETIME NOT NULL, is_survey_changed TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_4B879330B3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey_user (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, user_data_id INT DEFAULT NULL, role VARCHAR(32) NOT NULL, INDEX IDX_4B7AD682B3FE509D (survey_id), INDEX IDX_4B7AD6826FF8BF36 (user_data_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(64) NOT NULL, last_name VARCHAR(64) NOT NULL, patronymic VARCHAR(64) DEFAULT NULL, email VARCHAR(180) NOT NULL, phone VARCHAR(16) DEFAULT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649444F97DD (phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answer_variant ADD CONSTRAINT FK_B90370DC1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE bot_access ADD CONSTRAINT FK_FF6E7E8DCE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id)');
        $this->addSql('ALTER TABLE bot_access ADD CONSTRAINT FK_FF6E7E8D92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id)');
        $this->addSql('ALTER TABLE bot_user ADD CONSTRAINT FK_C355A3B92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id)');
        $this->addSql('ALTER TABLE bot_user ADD CONSTRAINT FK_C355A3B6FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE jump_condition ADD CONSTRAINT FK_73959DDFB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE jump_condition ADD CONSTRAINT FK_73959DDF30B4C8DC FOREIGN KEY (to_question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17FCE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id)');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17F1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17F54E42191 FOREIGN KEY (answer_variant_id) REFERENCES answer_variant (id)');
        $this->addSql('ALTER TABLE respondent_form ADD CONSTRAINT FK_4530324CB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE respondent_form ADD CONSTRAINT FK_4530324CCE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id)');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE social_network_config ADD CONSTRAINT FK_414DC5C992C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id)');
        $this->addSql('ALTER TABLE subcondition ADD CONSTRAINT FK_29B9237AA41E24D2 FOREIGN KEY (jump_condition_id) REFERENCES jump_condition (id)');
        $this->addSql('ALTER TABLE subcondition ADD CONSTRAINT FK_29B9237A54E42191 FOREIGN KEY (answer_variant_id) REFERENCES answer_variant (id)');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id)');
        $this->addSql('ALTER TABLE survey_access ADD CONSTRAINT FK_2E67E338CE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id)');
        $this->addSql('ALTER TABLE survey_access ADD CONSTRAINT FK_2E67E338B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE survey_iteration ADD CONSTRAINT FK_4B879330B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD682B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE survey_user ADD CONSTRAINT FK_4B7AD6826FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer_variant DROP FOREIGN KEY FK_B90370DC1E27F6BF');
        $this->addSql('ALTER TABLE bot_access DROP FOREIGN KEY FK_FF6E7E8DCE80CD19');
        $this->addSql('ALTER TABLE bot_access DROP FOREIGN KEY FK_FF6E7E8D92C1C487');
        $this->addSql('ALTER TABLE bot_user DROP FOREIGN KEY FK_C355A3B92C1C487');
        $this->addSql('ALTER TABLE bot_user DROP FOREIGN KEY FK_C355A3B6FF8BF36');
        $this->addSql('ALTER TABLE jump_condition DROP FOREIGN KEY FK_73959DDFB3FE509D');
        $this->addSql('ALTER TABLE jump_condition DROP FOREIGN KEY FK_73959DDF30B4C8DC');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EB3FE509D');
        $this->addSql('ALTER TABLE respondent_answer DROP FOREIGN KEY FK_FA2BD17FCE80CD19');
        $this->addSql('ALTER TABLE respondent_answer DROP FOREIGN KEY FK_FA2BD17F1E27F6BF');
        $this->addSql('ALTER TABLE respondent_answer DROP FOREIGN KEY FK_FA2BD17F54E42191');
        $this->addSql('ALTER TABLE respondent_form DROP FOREIGN KEY FK_4530324CB3FE509D');
        $this->addSql('ALTER TABLE respondent_form DROP FOREIGN KEY FK_4530324CCE80CD19');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBB3FE509D');
        $this->addSql('ALTER TABLE social_network_config DROP FOREIGN KEY FK_414DC5C992C1C487');
        $this->addSql('ALTER TABLE subcondition DROP FOREIGN KEY FK_29B9237AA41E24D2');
        $this->addSql('ALTER TABLE subcondition DROP FOREIGN KEY FK_29B9237A54E42191');
        $this->addSql('ALTER TABLE survey DROP FOREIGN KEY FK_AD5F9BFC92C1C487');
        $this->addSql('ALTER TABLE survey_access DROP FOREIGN KEY FK_2E67E338CE80CD19');
        $this->addSql('ALTER TABLE survey_access DROP FOREIGN KEY FK_2E67E338B3FE509D');
        $this->addSql('ALTER TABLE survey_iteration DROP FOREIGN KEY FK_4B879330B3FE509D');
        $this->addSql('ALTER TABLE survey_user DROP FOREIGN KEY FK_4B7AD682B3FE509D');
        $this->addSql('ALTER TABLE survey_user DROP FOREIGN KEY FK_4B7AD6826FF8BF36');
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
        $this->addSql('DROP TABLE user');
    }
}
