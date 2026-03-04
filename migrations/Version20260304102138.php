<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260304102138 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE generation ADD source VARCHAR(512) DEFAULT NULL, ADD tool_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE generation ADD CONSTRAINT FK_D3266C3B8F7B22CC FOREIGN KEY (tool_id) REFERENCES tool (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_D3266C3B8F7B22CC ON generation (tool_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE generation DROP FOREIGN KEY FK_D3266C3B8F7B22CC');
        $this->addSql('DROP INDEX IDX_D3266C3B8F7B22CC ON generation');
        $this->addSql('ALTER TABLE generation DROP source, DROP tool_id');
    }
}
