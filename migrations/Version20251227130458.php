<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251227130458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_pokemon table to track Pokémon variants owned by users';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_pokemon (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, pokemon_id INT NOT NULL, has_normal TINYINT DEFAULT 0 NOT NULL, has_shiny TINYINT DEFAULT 0 NOT NULL, has_shadow TINYINT DEFAULT 0 NOT NULL, has_purified TINYINT DEFAULT 0 NOT NULL, has_lucky TINYINT DEFAULT 0 NOT NULL, has_xxl TINYINT DEFAULT 0 NOT NULL, has_xxs TINYINT DEFAULT 0 NOT NULL, has_perfect TINYINT DEFAULT 0 NOT NULL, first_caught_at DATETIME NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_3AD18EF9A76ED395 (user_id), INDEX IDX_3AD18EF92FE71C3E (pokemon_id), UNIQUE INDEX user_pokemon_unique (user_id, pokemon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE user_pokemon ADD CONSTRAINT FK_3AD18EF9A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_pokemon ADD CONSTRAINT FK_3AD18EF92FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_pokemon DROP FOREIGN KEY FK_3AD18EF9A76ED395');
        $this->addSql('ALTER TABLE user_pokemon DROP FOREIGN KEY FK_3AD18EF92FE71C3E');
        $this->addSql('DROP TABLE user_pokemon');
    }
}
