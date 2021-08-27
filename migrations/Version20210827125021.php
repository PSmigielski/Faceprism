<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210827125021 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B33F0C4CF6');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B33F0C4CF6 FOREIGN KEY (li_post) REFERENCES post (po_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user CHANGE us_tag us_tag VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B33F0C4CF6');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B33F0C4CF6 FOREIGN KEY (li_post) REFERENCES post (po_id)');
        $this->addSql('ALTER TABLE user CHANGE us_tag us_tag VARCHAR(50) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
