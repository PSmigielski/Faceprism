<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211009182812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD po_page_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC6918559 FOREIGN KEY (po_author) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D8B35FE6B FOREIGN KEY (po_page_id) REFERENCES page (pa_id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_5A8A6C8D8B35FE6B ON post (po_page_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC6918559');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D8B35FE6B');
        $this->addSql('DROP INDEX IDX_5A8A6C8D8B35FE6B ON post');
        $this->addSql('ALTER TABLE post DROP po_page_id');
    }
}
