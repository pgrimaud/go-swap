<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251228181715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        // Create evolution_chain table
        $this->addSql('CREATE TABLE evolution_chain (id INT AUTO_INCREMENT NOT NULL, chain_id INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX chain_id_uniq (chain_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        // Add evolution_chain_id column to pokemon (will be populated by command later)
        $this->addSql('ALTER TABLE pokemon ADD evolution_chain_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE pokemon ADD CONSTRAINT FK_62DC90F3E417AC09 FOREIGN KEY (evolution_chain_id) REFERENCES evolution_chain (id)');
        $this->addSql('CREATE INDEX IDX_62DC90F3E417AC09 ON pokemon (evolution_chain_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE evolution_chain');
        $this->addSql('ALTER TABLE pokemon DROP FOREIGN KEY FK_62DC90F3E417AC09');
        $this->addSql('DROP INDEX IDX_62DC90F3E417AC09 ON pokemon');
    }
}
