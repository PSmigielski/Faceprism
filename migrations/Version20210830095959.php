<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210830095959 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE page (pa_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', pa_owner CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', pa_name VARCHAR(255) NOT NULL, pa_bio VARCHAR(255) NOT NULL, pa_follow_count INT NOT NULL, pa_profile_pic_url VARCHAR(255) NOT NULL, pa_banner_url VARCHAR(255) DEFAULT NULL, pa_email VARCHAR(255) DEFAULT NULL, pa_website VARCHAR(255) DEFAULT NULL, INDEX IDX_140AB6201029A2EB (pa_owner), PRIMARY KEY(pa_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6201029A2EB FOREIGN KEY (pa_owner) REFERENCES user (us_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE page');
    }
}
