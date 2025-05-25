<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250525144934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE move (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, power INT NOT NULL, energy INT NOT NULL, energy_gain INT NOT NULL, cooldown INT NOT NULL, buff_attack INT DEFAULT NULL, buff_defense INT DEFAULT NULL, buff_target VARCHAR(255) DEFAULT NULL, buff_chance DOUBLE PRECISION DEFAULT NULL, category VARCHAR(255) NOT NULL, class VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, INDEX IDX_EF3E3778C54C8C93 (type_id), UNIQUE INDEX slug_uniq (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE pokemon (id INT AUTO_INCREMENT NOT NULL, number INT NOT NULL, name VARCHAR(255) NOT NULL, attack INT NOT NULL, defense INT NOT NULL, stamina INT NOT NULL, hash VARCHAR(255) NOT NULL, shadow TINYINT(1) NOT NULL, slug VARCHAR(255) NOT NULL, UNIQUE INDEX slug_uniq (slug), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE move ADD CONSTRAINT FK_EF3E3778C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX slug_uniq ON type (slug)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE move DROP FOREIGN KEY FK_EF3E3778C54C8C93
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE move
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pokemon
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX slug_uniq ON type
        SQL);
    }
}
