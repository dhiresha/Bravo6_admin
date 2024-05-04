<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240504114753 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dish (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dish_food_item (dish_id INT NOT NULL, food_item_id INT NOT NULL, INDEX IDX_7045C99E148EB0CB (dish_id), INDEX IDX_7045C99E5DF08E66 (food_item_id), PRIMARY KEY(dish_id, food_item_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE dish_food_item ADD CONSTRAINT FK_7045C99E148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish_food_item ADD CONSTRAINT FK_7045C99E5DF08E66 FOREIGN KEY (food_item_id) REFERENCES food_item (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish_food_item DROP FOREIGN KEY FK_7045C99E148EB0CB');
        $this->addSql('ALTER TABLE dish_food_item DROP FOREIGN KEY FK_7045C99E5DF08E66');
        $this->addSql('DROP TABLE dish');
        $this->addSql('DROP TABLE dish_food_item');
    }
}
