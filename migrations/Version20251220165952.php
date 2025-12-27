<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251220165952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX slug_uniq (slug), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE type_effectiveness (id INT AUTO_INCREMENT NOT NULL, multiplier DOUBLE PRECISION NOT NULL, source_type_id INT NOT NULL, target_type_id INT NOT NULL, INDEX IDX_A30D7CC98C9334FB (source_type_id), INDEX IDX_A30D7CC9E2435F8 (target_type_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE type_effectiveness ADD CONSTRAINT FK_A30D7CC98C9334FB FOREIGN KEY (source_type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE type_effectiveness ADD CONSTRAINT FK_A30D7CC9E2435F8 FOREIGN KEY (target_type_id) REFERENCES type (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type_effectiveness DROP FOREIGN KEY FK_A30D7CC98C9334FB');
        $this->addSql('ALTER TABLE type_effectiveness DROP FOREIGN KEY FK_A30D7CC9E2435F8');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE type_effectiveness');
    }
}
