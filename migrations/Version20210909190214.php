<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210909190214 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE page_moderation (pa_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', pm_page_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', pa_owner CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', pm_page_role VARCHAR(50) NOT NULL, INDEX IDX_CB0A5B3AA5C3D6ED (pm_page_id), INDEX IDX_CB0A5B3A1029A2EB (pa_owner), PRIMARY KEY(pa_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE page_moderation ADD CONSTRAINT FK_CB0A5B3AA5C3D6ED FOREIGN KEY (pm_page_id) REFERENCES page (pa_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_moderation ADD CONSTRAINT FK_CB0A5B3A1029A2EB FOREIGN KEY (pa_owner) REFERENCES user (us_id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE page_moderation');
    }
}
