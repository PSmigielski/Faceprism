<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211011102500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_moderation DROP FOREIGN KEY FK_CB0A5B3A1029A2EB');
        $this->addSql('DROP INDEX IDX_CB0A5B3A1029A2EB ON page_moderation');
        $this->addSql('ALTER TABLE page_moderation CHANGE pa_owner pa_user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE page_moderation ADD CONSTRAINT FK_CB0A5B3AA43882E5 FOREIGN KEY (pa_user) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CB0A5B3AA43882E5 ON page_moderation (pa_user)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page_moderation DROP FOREIGN KEY FK_CB0A5B3AA43882E5');
        $this->addSql('DROP INDEX IDX_CB0A5B3AA43882E5 ON page_moderation');
        $this->addSql('ALTER TABLE page_moderation CHANGE pa_user pa_owner CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE page_moderation ADD CONSTRAINT FK_CB0A5B3A1029A2EB FOREIGN KEY (pa_owner) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_CB0A5B3A1029A2EB ON page_moderation (pa_owner)');
    }
}
