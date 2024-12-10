<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241206231434 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pvp_question (id INT AUTO_INCREMENT NOT NULL, pvp_quiz_id INT NOT NULL, question LONGTEXT NOT NULL, answers LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', valid_answer INT NOT NULL, good_answer TINYINT(1) DEFAULT NULL, INDEX IDX_2261571CA073A74 (pvp_quiz_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE pvp_question ADD CONSTRAINT FK_2261571CA073A74 FOREIGN KEY (pvp_quiz_id) REFERENCES pvp_quiz (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE pvp_question DROP FOREIGN KEY FK_2261571CA073A74');
        $this->addSql('DROP TABLE pvp_question');
    }
}
