<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260312080900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract ADD type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY `FK_14B784184A3353D8`');
        $this->addSql('ALTER TABLE photo ADD status VARCHAR(20) NOT NULL, ADD ip_address VARCHAR(45) DEFAULT NULL');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784184A3353D8 FOREIGN KEY (app_user_id) REFERENCES app_user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract DROP type');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784184A3353D8');
        $this->addSql('ALTER TABLE photo DROP status, DROP ip_address');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT `FK_14B784184A3353D8` FOREIGN KEY (app_user_id) REFERENCES user (id)');
    }
}
