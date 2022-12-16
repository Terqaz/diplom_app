<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221215145621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer_variant (id INT AUTO_INCREMENT NOT NULL, question_id INT NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_B90370DC1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bot (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(128) NOT NULL, description VARCHAR(1024) DEFAULT NULL, is_private TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bot_user (id INT AUTO_INCREMENT NOT NULL, bot_id INT NOT NULL, user_data_id INT NOT NULL, role VARCHAR(64) NOT NULL, INDEX IDX_C355A3B92C1C487 (bot_id), INDEX IDX_C355A3B6FF8BF36 (user_data_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, title VARCHAR(400) NOT NULL, type VARCHAR(32) NOT NULL, correct_answer VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_B6F7494E2B36786B (title), INDEX IDX_B6F7494EB3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE respondent (id INT AUTO_INCREMENT NOT NULL, telegram_id INT DEFAULT NULL, vkontakte_id INT DEFAULT NULL, email VARCHAR(180) DEFAULT NULL, phone VARCHAR(16) DEFAULT NULL, UNIQUE INDEX UNIQ_409B5150CC0B3066 (telegram_id), UNIQUE INDEX UNIQ_409B515089588C72 (vkontakte_id), UNIQUE INDEX UNIQ_409B5150E7927C74 (email), UNIQUE INDEX UNIQ_409B5150444F97DD (phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE respondent_access (id INT AUTO_INCREMENT NOT NULL, respondent_id INT NOT NULL, survey_id INT NOT NULL, property VARCHAR(32) NOT NULL, INDEX IDX_269FB00ECE80CD19 (respondent_id), INDEX IDX_269FB00EB3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE respondent_answer (id INT AUTO_INCREMENT NOT NULL, respondent_id INT NOT NULL, question_id INT NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_FA2BD17FCE80CD19 (respondent_id), INDEX IDX_FA2BD17F1E27F6BF (question_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE schedule (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, is_once TINYINT(1) NOT NULL, type VARCHAR(32) NOT NULL, repeat_values VARCHAR(128) NOT NULL, is_notice_before_start TINYINT(1) NOT NULL, last_repeat DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_5A3811FBB3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE social_network_connection (id INT AUTO_INCREMENT NOT NULL, bot_id INT NOT NULL, access_token VARCHAR(255) NOT NULL, webhook_url_path VARCHAR(255) DEFAULT NULL, INDEX IDX_A3F0BEFA92C1C487 (bot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE survey (id INT AUTO_INCREMENT NOT NULL, bot_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, description VARCHAR(1024) DEFAULT NULL, is_private TINYINT(1) NOT NULL, INDEX IDX_AD5F9BFC92C1C487 (bot_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, first_name VARCHAR(64) NOT NULL, last_name VARCHAR(64) NOT NULL, patronymic VARCHAR(64) DEFAULT NULL, phone VARCHAR(16) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649444F97DD (phone), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answer_variant ADD CONSTRAINT FK_B90370DC1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE bot_user ADD CONSTRAINT FK_C355A3B92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id)');
        $this->addSql('ALTER TABLE bot_user ADD CONSTRAINT FK_C355A3B6FF8BF36 FOREIGN KEY (user_data_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE respondent_access ADD CONSTRAINT FK_269FB00ECE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id)');
        $this->addSql('ALTER TABLE respondent_access ADD CONSTRAINT FK_269FB00EB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17FCE80CD19 FOREIGN KEY (respondent_id) REFERENCES respondent (id)');
        $this->addSql('ALTER TABLE respondent_answer ADD CONSTRAINT FK_FA2BD17F1E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('ALTER TABLE schedule ADD CONSTRAINT FK_5A3811FBB3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE social_network_connection ADD CONSTRAINT FK_A3F0BEFA92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id)');
        $this->addSql('ALTER TABLE survey ADD CONSTRAINT FK_AD5F9BFC92C1C487 FOREIGN KEY (bot_id) REFERENCES bot (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer_variant DROP FOREIGN KEY FK_B90370DC1E27F6BF');
        $this->addSql('ALTER TABLE bot_user DROP FOREIGN KEY FK_C355A3B92C1C487');
        $this->addSql('ALTER TABLE bot_user DROP FOREIGN KEY FK_C355A3B6FF8BF36');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494EB3FE509D');
        $this->addSql('ALTER TABLE respondent_access DROP FOREIGN KEY FK_269FB00ECE80CD19');
        $this->addSql('ALTER TABLE respondent_access DROP FOREIGN KEY FK_269FB00EB3FE509D');
        $this->addSql('ALTER TABLE respondent_answer DROP FOREIGN KEY FK_FA2BD17FCE80CD19');
        $this->addSql('ALTER TABLE respondent_answer DROP FOREIGN KEY FK_FA2BD17F1E27F6BF');
        $this->addSql('ALTER TABLE schedule DROP FOREIGN KEY FK_5A3811FBB3FE509D');
        $this->addSql('ALTER TABLE social_network_connection DROP FOREIGN KEY FK_A3F0BEFA92C1C487');
        $this->addSql('ALTER TABLE survey DROP FOREIGN KEY FK_AD5F9BFC92C1C487');
        $this->addSql('DROP TABLE answer_variant');
        $this->addSql('DROP TABLE bot');
        $this->addSql('DROP TABLE bot_user');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE respondent');
        $this->addSql('DROP TABLE respondent_access');
        $this->addSql('DROP TABLE respondent_answer');
        $this->addSql('DROP TABLE schedule');
        $this->addSql('DROP TABLE social_network_connection');
        $this->addSql('DROP TABLE survey');
        $this->addSql('DROP TABLE user');
    }
}
