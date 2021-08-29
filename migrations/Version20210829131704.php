<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210829131704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA824B40D');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA824B40D FOREIGN KEY (co_reply_to) REFERENCES comment (co_id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA824B40D');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA824B40D FOREIGN KEY (co_reply_to) REFERENCES comment (co_id)');
    }
}
