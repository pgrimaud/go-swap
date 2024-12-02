<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241201115745 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_pvp_pokemon (id INT AUTO_INCREMENT NOT NULL, pokemon_id INT NOT NULL, user_id INT NOT NULL, little_cup_rank INT NOT NULL, great_league_rank INT NOT NULL, ultra_league_rank INT NOT NULL, hidden TINYINT(1) DEFAULT 0 NOT NULL, INDEX IDX_44FCB2672FE71C3E (pokemon_id), INDEX IDX_44FCB267A76ED395 (user_id), UNIQUE INDEX pokemon_user_uniq (pokemon_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB2672FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)');
        $this->addSql('ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB267A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB2672FE71C3E');
        $this->addSql('ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB267A76ED395');
        $this->addSql('DROP TABLE user_pvp_pokemon');
    }
}
