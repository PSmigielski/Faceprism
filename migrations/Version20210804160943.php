<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210804160943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C53DCFBED');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C53DCFBED FOREIGN KEY (co_author) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3E815F632');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3E815F632 FOREIGN KEY (li_user) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC6918559');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC6918559 FOREIGN KEY (po_author) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748A8D93D649');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748A8D93D649 FOREIGN KEY (user) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE verify_email_request DROP FOREIGN KEY FK_2D9F91E7228FE0BA');
        $this->addSql('ALTER TABLE verify_email_request ADD CONSTRAINT FK_2D9F91E7228FE0BA FOREIGN KEY (ver_user_id) REFERENCES user (us_id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C53DCFBED');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C53DCFBED FOREIGN KEY (co_author) REFERENCES user (us_id)');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3E815F632');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3E815F632 FOREIGN KEY (li_user) REFERENCES user (us_id)');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC6918559');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC6918559 FOREIGN KEY (po_author) REFERENCES user (us_id)');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748A8D93D649');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748A8D93D649 FOREIGN KEY (user) REFERENCES user (us_id)');
        $this->addSql('ALTER TABLE verify_email_request DROP FOREIGN KEY FK_2D9F91E7228FE0BA');
        $this->addSql('ALTER TABLE verify_email_request ADD CONSTRAINT FK_2D9F91E7228FE0BA FOREIGN KEY (ver_user_id) REFERENCES user (us_id)');
    }
}
