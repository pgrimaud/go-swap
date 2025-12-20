<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251220170531 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE move (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, power INT NOT NULL, energy INT NOT NULL, energy_gain INT NOT NULL, cooldown INT NOT NULL, buff_attack INT DEFAULT NULL, buff_defense INT DEFAULT NULL, buff_target VARCHAR(255) DEFAULT NULL, buff_chance DOUBLE PRECISION DEFAULT NULL, category VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, type_id INT NOT NULL, INDEX IDX_EF3E3778C54C8C93 (type_id), UNIQUE INDEX slug_uniq (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE move ADD CONSTRAINT FK_EF3E3778C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE move DROP FOREIGN KEY FK_EF3E3778C54C8C93');
        $this->addSql('DROP TABLE move');
    }
}
