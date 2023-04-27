<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230426185818 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer_variant ADD serial_number SMALLINT DEFAULT NULL, DROP code');
        $this->addSql('ALTER TABLE bot_user CHANGE role role VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE question ADD is_required TINYINT(1) DEFAULT 1 NOT NULL, ADD can_give_own_answer TINYINT(1) DEFAULT 0 NOT NULL, ADD max_variants SMALLINT DEFAULT NULL, DROP encoding_type');
        $this->addSql('ALTER TABLE survey ADD is_enabled TINYINT(1) NOT NULL, DROP is_test, DROP is_shuffle_variants');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer_variant ADD code INT DEFAULT NULL, DROP serial_number');
        $this->addSql('ALTER TABLE survey ADD is_test TINYINT(1) DEFAULT 0 NOT NULL, ADD is_shuffle_variants TINYINT(1) DEFAULT 0 NOT NULL, DROP is_enabled');
        $this->addSql('ALTER TABLE question ADD encoding_type VARCHAR(32) DEFAULT NULL, DROP is_required, DROP can_give_own_answer, DROP max_variants');
        $this->addSql('ALTER TABLE bot_user CHANGE role role VARCHAR(64) NOT NULL');
    }
}
