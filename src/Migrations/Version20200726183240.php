<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200726183240 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE report_user (id INT AUTO_INCREMENT NOT NULL, reported_id INT NOT NULL, auteur_id INT NOT NULL, motif VARCHAR(255) NOT NULL, message LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, INDEX IDX_FEBF3BB294BDEEB6 (reported_id), INDEX IDX_FEBF3BB260BB6FE6 (auteur_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE report_user ADD CONSTRAINT FK_FEBF3BB294BDEEB6 FOREIGN KEY (reported_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE report_user ADD CONSTRAINT FK_FEBF3BB260BB6FE6 FOREIGN KEY (auteur_id) REFERENCES user (id)');
        $this->addSql('CREATE FULLTEXT INDEX IDX_23A0E662B36786BFEC530A9F5005424 ON article (title, content, introduction)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE report_user');
        $this->addSql('DROP INDEX IDX_23A0E662B36786BFEC530A9F5005424 ON article');
    }
}
