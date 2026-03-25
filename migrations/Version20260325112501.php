<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260325112501 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding ADD wedding_room_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE wedding ADD CONSTRAINT FK_5BC25C963E26EE3E FOREIGN KEY (wedding_room_id) REFERENCES wedding_room (id)');
        $this->addSql('CREATE INDEX IDX_5BC25C963E26EE3E ON wedding (wedding_room_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wedding DROP FOREIGN KEY FK_5BC25C963E26EE3E');
        $this->addSql('DROP INDEX IDX_5BC25C963E26EE3E ON wedding');
        $this->addSql('ALTER TABLE wedding DROP wedding_room_id');
    }
}
