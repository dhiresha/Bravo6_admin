<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240504112101 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE food_category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE food_item ADD food_category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE food_item ADD CONSTRAINT FK_AA3C8DCFB3F04B2C FOREIGN KEY (food_category_id) REFERENCES food_category (id)');
        $this->addSql('CREATE INDEX IDX_AA3C8DCFB3F04B2C ON food_item (food_category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE food_item DROP FOREIGN KEY FK_AA3C8DCFB3F04B2C');
        $this->addSql('DROP TABLE food_category');
        $this->addSql('DROP INDEX IDX_AA3C8DCFB3F04B2C ON food_item');
        $this->addSql('ALTER TABLE food_item DROP food_category_id');
    }
}
