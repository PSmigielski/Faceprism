<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211012105506 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE follow MODIFY id INT NOT NULL');
        $this->addSql('ALTER TABLE follow DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE follow ADD fo_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', DROP id');
        $this->addSql('ALTER TABLE follow ADD PRIMARY KEY (fo_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE follow ADD id INT AUTO_INCREMENT NOT NULL, DROP fo_id, DROP PRIMARY KEY, ADD PRIMARY KEY (id)');
    }
}
