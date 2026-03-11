<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260310100222 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE contract (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, version INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wedding (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, wedding_code VARCHAR(50) NOT NULL, wedding_date DATETIME NOT NULL, longitude DOUBLE PRECISION NOT NULL, wedding_hall_id INT DEFAULT NULL, active_contract_id INT DEFAULT NULL, INDEX IDX_5BC25C96389E517B (wedding_hall_id), INDEX IDX_5BC25C96718915B6 (active_contract_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE wedding_hall (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, latitude DOUBLE PRECISION NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96389E517B FOREIGN KEY (wedding_hall_id) REFERENCES wedding_hall (id)');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C96718915B6 FOREIGN KEY (active_contract_id) REFERENCES contract (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96389E517B');
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C96718915B6');
        $this->addSql('DROP TABLE contract');
        $this->addSql('DROP TABLE wedding');
        $this->addSql('DROP TABLE wedding_hall');
    }
}
