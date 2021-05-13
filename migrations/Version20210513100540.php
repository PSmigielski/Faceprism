<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210513100540 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE post (po_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', po_author CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', po_created_at DATETIME NOT NULL, po_edited_at DATETIME DEFAULT NULL, po_text TEXT DEFAULT NULL, po_image VARCHAR(255) DEFAULT NULL, INDEX IDX_5A8A6C8DC6918559 (po_author), PRIMARY KEY(po_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748A8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (us_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', us_email VARCHAR(180) NOT NULL, us_roles JSON NOT NULL, us_password VARCHAR(255) NOT NULL, us_name VARCHAR(30) NOT NULL, us_surname VARCHAR(100) NOT NULL, us_date_of_birth DATE NOT NULL, us_gender VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_8D93D649807ABB60 (us_email), PRIMARY KEY(us_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC6918559 FOREIGN KEY (po_author) REFERENCES user (us_id)');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748A8D93D649 FOREIGN KEY (user) REFERENCES user (us_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC6918559');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748A8D93D649');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE user');
    }
}
