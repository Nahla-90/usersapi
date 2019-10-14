<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191014011820 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE user_id_seq CASCADE');
        $this->addSql('DROP TABLE "user"');
        $this->addSql('ALTER TABLE urls ADD client_id INT NOT NULL');
        $this->addSql('ALTER TABLE urls DROP status');
        $this->addSql('ALTER TABLE urls ADD CONSTRAINT FK_2A9437A119EB6921 FOREIGN KEY (client_id) REFERENCES client (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_2A9437A119EB6921 ON urls (client_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE user_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE "user" (id INT NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(64) NOT NULL, mobile_number VARCHAR(15) NOT NULL, username VARCHAR(20) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE urls DROP CONSTRAINT FK_2A9437A119EB6921');
        $this->addSql('DROP INDEX IDX_2A9437A119EB6921');
        $this->addSql('ALTER TABLE urls ADD status VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE urls DROP client_id');
    }
}
