<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241205105646 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE type_effectiveness (id INT AUTO_INCREMENT NOT NULL, source_type_id INT NOT NULL, target_type_id INT NOT NULL, multiplier DOUBLE PRECISION NOT NULL, INDEX IDX_A30D7CC98C9334FB (source_type_id), INDEX IDX_A30D7CC9E2435F8 (target_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE type_effectiveness ADD CONSTRAINT FK_A30D7CC98C9334FB FOREIGN KEY (source_type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE type_effectiveness ADD CONSTRAINT FK_A30D7CC9E2435F8 FOREIGN KEY (target_type_id) REFERENCES type (id)');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE type_effectiveness DROP FOREIGN KEY FK_A30D7CC98C9334FB');
        $this->addSql('ALTER TABLE type_effectiveness DROP FOREIGN KEY FK_A30D7CC9E2435F8');
        $this->addSql('DROP TABLE type_effectiveness');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
