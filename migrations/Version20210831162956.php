<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210831162956 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6201029A2EB');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6201029A2EB FOREIGN KEY (pa_owner) REFERENCES user (us_id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB6201029A2EB');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB6201029A2EB FOREIGN KEY (pa_owner) REFERENCES user (us_id)');
    }
}
