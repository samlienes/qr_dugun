<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325095349 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE app_user (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(12) NOT NULL, last_name VARCHAR(12) NOT NULL, phone_number VARCHAR(20) NOT NULL, is_verified TINYINT NOT NULL, verification_code VARCHAR(6) DEFAULT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, wedding_hall_id INT DEFAULT NULL, INDEX IDX_88BDF3E9389E517B (wedding_hall_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE app_user_wedding (app_user_id INT NOT NULL, wedding_id INT NOT NULL, INDEX IDX_C141581D4A3353D8 (app_user_id), INDEX IDX_C141581DFCBBB0ED (wedding_id), PRIMARY KEY (app_user_id, wedding_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, type VARCHAR(50) NOT NULL, version VARCHAR(10) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, filename VARCHAR(255) NOT NULL, uploaded_at DATETIME NOT NULL, status VARCHAR(20) NOT NULL, ip_address VARCHAR(45) DEFAULT NULL, message LONGTEXT DEFAULT NULL, wedding_id INT NOT NULL, app_user_id INT NOT NULL, INDEX IDX_14B78418FCBBB0ED (wedding_id), INDEX IDX_14B784184A3353D8 (app_user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_contract (id INT AUTO_INCREMENT NOT NULL, ip_address VARCHAR(45) NOT NULL, accepted_at DATETIME NOT NULL, app_user_id INT NOT NULL, contract_id INT NOT NULL, INDEX IDX_902CC594A3353D8 (app_user_id), INDEX IDX_902CC592576E0FD (contract_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wedding (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATETIME DEFAULT NULL, wedding_hall_id INT DEFAULT NULL, INDEX IDX_5BC25C96389E517B (wedding_hall_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wedding_hall (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, longitude DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wedding_room (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, wedding_hall_id INT NOT NULL, INDEX IDX_3B43859B389E517B (wedding_hall_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE app_user ADD CONSTRAINT FK_88BDF3E9389E517B FOREIGN KEY (wedding_hall_id) REFERENCES wedding_hall (id)');
        $this->addSql('ALTER TABLE app_user_wedding ADD CONSTRAINT FK_C141581D4A3353D8 FOREIGN KEY (app_user_id) REFERENCES app_user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE app_user_wedding ADD CONSTRAINT FK_C141581DFCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B78418FCBBB0ED FOREIGN KEY (wedding_id) REFERENCES wedding (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784184A3353D8 FOREIGN KEY (app_user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE user_contract ADD CONSTRAINT FK_902CC594A3353D8 FOREIGN KEY (app_user_id) REFERENCES app_user (id)');
        $this->addSql('ALTER TABLE user_contract ADD CONSTRAINT FK_902CC592576E0FD FOREIGN KEY (contract_id) REFERENCES contract (id)');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96389E517B FOREIGN KEY (wedding_hall_id) REFERENCES wedding_hall (id)');
        $this->addSql('ALTER TABLE wedding_room ADD CONSTRAINT FK_3B43859B389E517B FOREIGN KEY (wedding_hall_id) REFERENCES wedding_hall (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE app_user DROP FOREIGN KEY FK_88BDF3E9389E517B');
        $this->addSql('ALTER TABLE app_user_wedding DROP FOREIGN KEY FK_C141581D4A3353D8');
        $this->addSql('ALTER TABLE app_user_wedding DROP FOREIGN KEY FK_C141581DFCBBB0ED');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B78418FCBBB0ED');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784184A3353D8');
        $this->addSql('ALTER TABLE user_contract DROP FOREIGN KEY FK_902CC594A3353D8');
        $this->addSql('ALTER TABLE user_contract DROP FOREIGN KEY FK_902CC592576E0FD');
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96389E517B');
        $this->addSql('ALTER TABLE wedding_room DROP FOREIGN KEY FK_3B43859B389E517B');
        $this->addSql('DROP TABLE app_user');
        $this->addSql('DROP TABLE app_user_wedding');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE photo');
        $this->addSql('DROP TABLE user_contract');
        $this->addSql('DROP TABLE wedding');
        $this->addSql('DROP TABLE wedding_hall');
        $this->addSql('DROP TABLE wedding_room');
    }
}
