<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250301172521 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pvp_question RENAME INDEX idx_2261571ca073a74 TO IDX_C17F4233A073A74');
        $this->addSql('ALTER TABLE pvp_quiz RENAME INDEX idx_c796d48ba76ed395 TO IDX_72FDDBBDA76ED395');
        $this->addSql('DROP INDEX pokemon_user_uniq ON user_pvp_pokemon');
        $this->addSql('ALTER TABLE user_pvp_pokemon ADD fast_move_id INT NOT NULL, ADD charged_move1_id INT NOT NULL, ADD charged_move2_id INT DEFAULT NULL, ADD `rank` INT NOT NULL, ADD league VARCHAR(255) NOT NULL, ADD shadow TINYINT(1) NOT NULL, DROP little_cup_rank, DROP great_league_rank, DROP ultra_league_rank, DROP hidden');
        $this->addSql('ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB267E17E9581 FOREIGN KEY (fast_move_id) REFERENCES move (id)');
        $this->addSql('ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB267D97FD5E5 FOREIGN KEY (charged_move1_id) REFERENCES move (id)');
        $this->addSql('ALTER TABLE user_pvp_pokemon ADD CONSTRAINT FK_44FCB267CBCA7A0B FOREIGN KEY (charged_move2_id) REFERENCES move (id)');
        $this->addSql('CREATE INDEX IDX_44FCB267E17E9581 ON user_pvp_pokemon (fast_move_id)');
        $this->addSql('CREATE INDEX IDX_44FCB267D97FD5E5 ON user_pvp_pokemon (charged_move1_id)');
        $this->addSql('CREATE INDEX IDX_44FCB267CBCA7A0B ON user_pvp_pokemon (charged_move2_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pvp_question RENAME INDEX idx_c17f4233a073a74 TO IDX_2261571CA073A74');
        $this->addSql('ALTER TABLE pvp_quiz RENAME INDEX idx_72fddbbda76ed395 TO IDX_C796D48BA76ED395');
        $this->addSql('ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB267E17E9581');
        $this->addSql('ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB267D97FD5E5');
        $this->addSql('ALTER TABLE user_pvp_pokemon DROP FOREIGN KEY FK_44FCB267CBCA7A0B');
        $this->addSql('DROP INDEX IDX_44FCB267E17E9581 ON user_pvp_pokemon');
        $this->addSql('DROP INDEX IDX_44FCB267D97FD5E5 ON user_pvp_pokemon');
        $this->addSql('DROP INDEX IDX_44FCB267CBCA7A0B ON user_pvp_pokemon');
        $this->addSql('ALTER TABLE user_pvp_pokemon ADD little_cup_rank INT NOT NULL, ADD great_league_rank INT NOT NULL, ADD ultra_league_rank INT NOT NULL, ADD hidden TINYINT(1) DEFAULT 0 NOT NULL, DROP fast_move_id, DROP charged_move1_id, DROP charged_move2_id, DROP `rank`, DROP league, DROP shadow');
        $this->addSql('CREATE UNIQUE INDEX pokemon_user_uniq ON user_pvp_pokemon (pokemon_id, user_id)');
    }
}
