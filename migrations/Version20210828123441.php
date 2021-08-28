<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210828123441 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (co_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', co_author CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', co_post CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', co_reply_to CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', co_text VARCHAR(255) NOT NULL, co_created_at DATETIME NOT NULL, co_edited_at DATETIME DEFAULT NULL, INDEX IDX_9474526C53DCFBED (co_author), INDEX IDX_9474526C1F1DDF02 (co_post), INDEX IDX_9474526CA824B40D (co_reply_to), PRIMARY KEY(co_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE friend (fr_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fr_user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fr_friend CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fr_accept_date DATETIME NOT NULL, fr_is_blocked TINYINT(1) NOT NULL, INDEX IDX_55EEAC6125745B24 (fr_user), INDEX IDX_55EEAC6116B4EEF1 (fr_friend), PRIMARY KEY(fr_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE friend_request (fr_req_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fr_req_user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fr_req_friend CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', fr_req_request_date DATETIME NOT NULL, fr_req_accepted TINYINT(1) DEFAULT \'0\' NOT NULL, INDEX IDX_F284D946DA04C11 (fr_req_user), INDEX IDX_F284D943756E154 (fr_req_friend), PRIMARY KEY(fr_req_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `like` (li_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', li_user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', li_post CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_AC6340B3E815F632 (li_user), INDEX IDX_AC6340B33F0C4CF6 (li_post), PRIMARY KEY(li_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (po_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', po_author CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', po_created_at DATETIME NOT NULL, po_edited_at DATETIME DEFAULT NULL, po_text TEXT DEFAULT NULL, po_file_url VARCHAR(255) DEFAULT NULL, po_like_count INT NOT NULL, po_comment_count INT NOT NULL, INDEX IDX_5A8A6C8DC6918559 (po_author), PRIMARY KEY(po_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_tokens (id INT AUTO_INCREMENT NOT NULL, refresh_token VARCHAR(128) NOT NULL, username VARCHAR(255) NOT NULL, valid DATETIME NOT NULL, UNIQUE INDEX UNIQ_9BACE7E1C74F2195 (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reset_password_request (id INT AUTO_INCREMENT NOT NULL, user CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', selector VARCHAR(20) NOT NULL, hashed_token VARCHAR(100) NOT NULL, requested_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', expires_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7CE748A8D93D649 (user), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (us_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', us_email VARCHAR(180) NOT NULL, us_roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', us_password VARCHAR(255) NOT NULL, us_name VARCHAR(30) NOT NULL, us_surname VARCHAR(100) NOT NULL, us_date_of_birth DATE NOT NULL, us_gender VARCHAR(20) NOT NULL, us_verified TINYINT(1) NOT NULL, us_profile_pic_url VARCHAR(255) NOT NULL, us_banner_url VARCHAR(255) DEFAULT NULL, us_bio VARCHAR(255) DEFAULT NULL, us_tag VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649807ABB60 (us_email), PRIMARY KEY(us_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE verify_email_request (ver_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', ver_user_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', ver_requested_at DATETIME NOT NULL, ver_expires_at DATETIME NOT NULL, INDEX IDX_2D9F91E7228FE0BA (ver_user_id), PRIMARY KEY(ver_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C53DCFBED FOREIGN KEY (co_author) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C1F1DDF02 FOREIGN KEY (co_post) REFERENCES post (po_id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA824B40D FOREIGN KEY (co_reply_to) REFERENCES comment (co_id)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC6125745B24 FOREIGN KEY (fr_user) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC6116B4EEF1 FOREIGN KEY (fr_friend) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE friend_request ADD CONSTRAINT FK_F284D946DA04C11 FOREIGN KEY (fr_req_user) REFERENCES user (us_id)');
        $this->addSql('ALTER TABLE friend_request ADD CONSTRAINT FK_F284D943756E154 FOREIGN KEY (fr_req_friend) REFERENCES user (us_id)');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B3E815F632 FOREIGN KEY (li_user) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `like` ADD CONSTRAINT FK_AC6340B33F0C4CF6 FOREIGN KEY (li_post) REFERENCES post (po_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DC6918559 FOREIGN KEY (po_author) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reset_password_request ADD CONSTRAINT FK_7CE748A8D93D649 FOREIGN KEY (user) REFERENCES user (us_id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE verify_email_request ADD CONSTRAINT FK_2D9F91E7228FE0BA FOREIGN KEY (ver_user_id) REFERENCES user (us_id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA824B40D');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C1F1DDF02');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B33F0C4CF6');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C53DCFBED');
        $this->addSql('ALTER TABLE friend DROP FOREIGN KEY FK_55EEAC6125745B24');
        $this->addSql('ALTER TABLE friend DROP FOREIGN KEY FK_55EEAC6116B4EEF1');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_F284D946DA04C11');
        $this->addSql('ALTER TABLE friend_request DROP FOREIGN KEY FK_F284D943756E154');
        $this->addSql('ALTER TABLE `like` DROP FOREIGN KEY FK_AC6340B3E815F632');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DC6918559');
        $this->addSql('ALTER TABLE reset_password_request DROP FOREIGN KEY FK_7CE748A8D93D649');
        $this->addSql('ALTER TABLE verify_email_request DROP FOREIGN KEY FK_2D9F91E7228FE0BA');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE friend');
        $this->addSql('DROP TABLE friend_request');
        $this->addSql('DROP TABLE `like`');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE refresh_tokens');
        $this->addSql('DROP TABLE reset_password_request');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE verify_email_request');
    }
}
