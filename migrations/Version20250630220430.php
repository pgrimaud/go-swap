<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630220430 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE type_effectiveness (id INT AUTO_INCREMENT NOT NULL, source_type_id INT NOT NULL, target_type_id INT NOT NULL, multiplier DOUBLE PRECISION NOT NULL, INDEX IDX_A30D7CC98C9334FB (source_type_id), INDEX IDX_A30D7CC9E2435F8 (target_type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_effectiveness ADD CONSTRAINT FK_A30D7CC98C9334FB FOREIGN KEY (source_type_id) REFERENCES type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_effectiveness ADD CONSTRAINT FK_A30D7CC9E2435F8 FOREIGN KEY (target_type_id) REFERENCES type (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon ADD league_rank INT NOT NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE type_effectiveness DROP FOREIGN KEY FK_A30D7CC98C9334FB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type_effectiveness DROP FOREIGN KEY FK_A30D7CC9E2435F8
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE type_effectiveness
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon DROP league_rank
        SQL);
    }
}
