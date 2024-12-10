<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241206232004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pvp_question ADD status VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE pvp_question RENAME INDEX idx_2261571ca073a74 TO IDX_C17F4233A073A74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pvp_question DROP status');
        $this->addSql('ALTER TABLE pvp_question RENAME INDEX idx_c17f4233a073a74 TO IDX_2261571CA073A74');
    }
}
