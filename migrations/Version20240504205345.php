<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240504205345 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE food_item ADD media_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE food_item ADD CONSTRAINT FK_AA3C8DCFEA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_AA3C8DCFEA9FDD75 ON food_item (media_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE food_item DROP FOREIGN KEY FK_AA3C8DCFEA9FDD75');
        $this->addSql('DROP INDEX UNIQ_AA3C8DCFEA9FDD75 ON food_item');
        $this->addSql('ALTER TABLE food_item DROP media_id');
    }
}
