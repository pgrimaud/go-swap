<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241220123323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pokemon_move (id INT AUTO_INCREMENT NOT NULL, pokemon_id INT NOT NULL, move_id INT NOT NULL, elite TINYINT(1) NOT NULL, INDEX IDX_D397493B2FE71C3E (pokemon_id), INDEX IDX_D397493B6DC541A8 (move_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pokemon_move ADD CONSTRAINT FK_D397493B2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)');
        $this->addSql('ALTER TABLE pokemon_move ADD CONSTRAINT FK_D397493B6DC541A8 FOREIGN KEY (move_id) REFERENCES move (id)');
        $this->addSql('DROP INDEX api_id_uniq ON move');
        $this->addSql('ALTER TABLE move DROP api_id');
        $this->addSql('ALTER TABLE pokemon ADD slug VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE pvp_question ADD user_answer INT NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pokemon_move DROP FOREIGN KEY FK_D397493B2FE71C3E');
        $this->addSql('ALTER TABLE pokemon_move DROP FOREIGN KEY FK_D397493B6DC541A8');
        $this->addSql('DROP TABLE pokemon_move');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE pvp_question DROP user_answer');
        $this->addSql('ALTER TABLE pokemon DROP slug');
        $this->addSql('ALTER TABLE move ADD api_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX api_id_uniq ON move (api_id)');
    }
}
