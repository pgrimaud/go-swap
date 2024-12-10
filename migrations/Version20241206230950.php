<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241206230950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pvp_quiz ADD number_of_questions INT NOT NULL');
        $this->addSql('ALTER TABLE pvp_quiz RENAME INDEX idx_c796d48ba76ed395 TO IDX_72FDDBBDA76ED395');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pvp_quiz DROP number_of_questions');
        $this->addSql('ALTER TABLE pvp_quiz RENAME INDEX idx_72fddbbda76ed395 TO IDX_C796D48BA76ED395');
    }
}
