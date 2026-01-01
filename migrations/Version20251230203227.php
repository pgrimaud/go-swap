<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251230203227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pvp_ranking (id INT AUTO_INCREMENT NOT NULL, league VARCHAR(20) NOT NULL, `rank` INT NOT NULL, score NUMERIC(5, 2) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, pvp_version_id INT NOT NULL, pokemon_id INT NOT NULL, fast_move_id INT DEFAULT NULL, charged_move1_id INT DEFAULT NULL, charged_move2_id INT DEFAULT NULL, INDEX IDX_F83D2D146FF8EBE (pvp_version_id), INDEX IDX_F83D2D12FE71C3E (pokemon_id), INDEX IDX_F83D2D1E17E9581 (fast_move_id), INDEX IDX_F83D2D1D97FD5E5 (charged_move1_id), INDEX IDX_F83D2D1CBCA7A0B (charged_move2_id), UNIQUE INDEX version_pokemon_league_uniq (pvp_version_id, pokemon_id, league), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE pvp_ranking ADD CONSTRAINT FK_F83D2D146FF8EBE FOREIGN KEY (pvp_version_id) REFERENCES pvp_version (id)');
        $this->addSql('ALTER TABLE pvp_ranking ADD CONSTRAINT FK_F83D2D12FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)');
        $this->addSql('ALTER TABLE pvp_ranking ADD CONSTRAINT FK_F83D2D1E17E9581 FOREIGN KEY (fast_move_id) REFERENCES move (id)');
        $this->addSql('ALTER TABLE pvp_ranking ADD CONSTRAINT FK_F83D2D1D97FD5E5 FOREIGN KEY (charged_move1_id) REFERENCES move (id)');
        $this->addSql('ALTER TABLE pvp_ranking ADD CONSTRAINT FK_F83D2D1CBCA7A0B FOREIGN KEY (charged_move2_id) REFERENCES move (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pvp_ranking DROP FOREIGN KEY FK_F83D2D146FF8EBE');
        $this->addSql('ALTER TABLE pvp_ranking DROP FOREIGN KEY FK_F83D2D12FE71C3E');
        $this->addSql('ALTER TABLE pvp_ranking DROP FOREIGN KEY FK_F83D2D1E17E9581');
        $this->addSql('ALTER TABLE pvp_ranking DROP FOREIGN KEY FK_F83D2D1D97FD5E5');
        $this->addSql('ALTER TABLE pvp_ranking DROP FOREIGN KEY FK_F83D2D1CBCA7A0B');
        $this->addSql('DROP TABLE pvp_ranking');
    }
}
