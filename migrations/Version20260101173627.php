<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260101173627 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE custom_list (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, is_public TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX IDX_45BE30E5A76ED395 (user_id), UNIQUE INDEX custom_list_uid_unique (uid), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE custom_list_pokemon (id INT AUTO_INCREMENT NOT NULL, position INT DEFAULT 0 NOT NULL, added_at DATETIME NOT NULL, custom_list_id INT NOT NULL, pokemon_id INT NOT NULL, INDEX IDX_C5145AE53AF77F46 (custom_list_id), INDEX IDX_C5145AE52FE71C3E (pokemon_id), UNIQUE INDEX custom_list_pokemon_unique (custom_list_id, pokemon_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE custom_list ADD CONSTRAINT FK_45BE30E5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE custom_list_pokemon ADD CONSTRAINT FK_C5145AE53AF77F46 FOREIGN KEY (custom_list_id) REFERENCES custom_list (id)');
        $this->addSql('ALTER TABLE custom_list_pokemon ADD CONSTRAINT FK_C5145AE52FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE custom_list DROP FOREIGN KEY FK_45BE30E5A76ED395');
        $this->addSql('ALTER TABLE custom_list_pokemon DROP FOREIGN KEY FK_C5145AE53AF77F46');
        $this->addSql('ALTER TABLE custom_list_pokemon DROP FOREIGN KEY FK_C5145AE52FE71C3E');
        $this->addSql('DROP TABLE custom_list');
        $this->addSql('DROP TABLE custom_list_pokemon');
    }
}
