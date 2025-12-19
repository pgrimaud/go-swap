<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250618203753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE pokemon_type (pokemon_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_B077296A2FE71C3E (pokemon_id), INDEX IDX_B077296AC54C8C93 (type_id), PRIMARY KEY(pokemon_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE pokemon_move (id INT AUTO_INCREMENT NOT NULL, pokemon_id INT NOT NULL, move_id INT NOT NULL, elite TINYINT(1) NOT NULL, INDEX IDX_D397493B2FE71C3E (pokemon_id), INDEX IDX_D397493B6DC541A8 (move_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user_pvp_pokemon (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, pokemon_id INT NOT NULL, fast_move_id INT NOT NULL, charged_move1_id INT NOT NULL, charged_move2_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, league VARCHAR(255) NOT NULL, attack INT NOT NULL, defense INT NOT NULL, stamina INT NOT NULL, INDEX IDX_44FCB267A76ED395 (user_id), INDEX IDX_44FCB2672FE71C3E (pokemon_id), INDEX IDX_44FCB267E17E9581 (fast_move_id), INDEX IDX_44FCB267D97FD5E5 (charged_move1_id), INDEX IDX_44FCB267CBCA7A0B (charged_move2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon_type ADD CONSTRAINT FK_B077296A2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon_type ADD CONSTRAINT FK_B077296AC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon_move ADD CONSTRAINT FK_D397493B2FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon_move ADD CONSTRAINT FK_D397493B6DC541A8 FOREIGN KEY (move_id) REFERENCES move (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB267A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB2672FE71C3E FOREIGN KEY (pokemon_id) REFERENCES pokemon (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB267E17E9581 FOREIGN KEY (fast_move_id) REFERENCES move (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB267D97FD5E5 FOREIGN KEY (charged_move1_id) REFERENCES move (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB267CBCA7A0B FOREIGN KEY (charged_move2_id) REFERENCES move (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE move ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon ADD picture VARCHAR(255) DEFAULT NULL, ADD shiny_picture VARCHAR(255) DEFAULT NULL, ADD generation VARCHAR(255) NOT NULL, ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type ADD created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', ADD updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)'
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon_type DROP FOREIGN KEY FK_B077296A2FE71C3E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon_type DROP FOREIGN KEY FK_B077296AC54C8C93
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon_move DROP FOREIGN KEY FK_D397493B2FE71C3E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon_move DROP FOREIGN KEY FK_D397493B6DC541A8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB267A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB2672FE71C3E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB267E17E9581
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB267D97FD5E5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB267CBCA7A0B
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pokemon_type
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE pokemon_move
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_pvp_pokemon
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE pokemon DROP picture, DROP shiny_picture, DROP generation, DROP created_at, DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE type DROP created_at, DROP updated_at
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE move DROP created_at, DROP updated_at
        SQL);
    }
}
