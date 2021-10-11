<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211011103126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_moderation ADD CONSTRAINT FK_CB0A5B3A4A6322F7 FOREIGN KEY (pm_page) REFERENCES page (pa_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE page_moderation ADD CONSTRAINT FK_CB0A5B3AD3FA429E FOREIGN KEY (pm_user) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CB0A5B3A4A6322F7 ON page_moderation (pm_page)');
        $this->addSql('CREATE INDEX IDX_CB0A5B3AD3FA429E ON page_moderation (pm_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_moderation DROP FOREIGN KEY FK_CB0A5B3A4A6322F7');
        $this->addSql('ALTER TABLE page_moderation DROP FOREIGN KEY FK_CB0A5B3AD3FA429E');
        $this->addSql('DROP INDEX IDX_CB0A5B3A4A6322F7 ON page_moderation');
        $this->addSql('DROP INDEX IDX_CB0A5B3AD3FA429E ON page_moderation');
    }
}
