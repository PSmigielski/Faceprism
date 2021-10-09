<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211009191926 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D8B35FE6B');
        $this->addSql('DROP INDEX IDX_5A8A6C8D8B35FE6B ON post');
        $this->addSql('ALTER TABLE post CHANGE po_page_id po_page CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D7AB83FC FOREIGN KEY (po_page) REFERENCES page (pa_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D7AB83FC ON post (po_page)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D7AB83FC');
        $this->addSql('DROP INDEX IDX_5A8A6C8D7AB83FC ON post');
        $this->addSql('ALTER TABLE post CHANGE po_page po_page_id CHAR(36) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D8B35FE6B FOREIGN KEY (po_page_id) REFERENCES page (pa_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D8B35FE6B ON post (po_page_id)');
    }
}
