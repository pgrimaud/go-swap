<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230503144544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_pokemon (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, pokemon_id INT NOT NULL, normal TINYINT(1) NOT NULL, shiny TINYINT(1) NOT NULL, lucky TINYINT(1) NOT NULL, three_stars TINYINT(1) NOT NULL, INDEX IDX_3AD18EF9A76ED395 (user_id), INDEX IDX_3AD18EF92FE71C3E (pokemon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
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
