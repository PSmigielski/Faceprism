<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210520223130 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE friend (fr_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fr_user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fr_friend CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_55EEAC6125745B24 (fr_user), INDEX IDX_55EEAC6116B4EEF1 (fr_friend), PRIMARY KEY(fr_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC6125745B24 FOREIGN KEY (fr_user) REFERENCES user (us_id)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC6116B4EEF1 FOREIGN KEY (fr_friend) REFERENCES user (us_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE friend');
    }
}
