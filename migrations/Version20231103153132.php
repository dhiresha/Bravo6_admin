<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231103153132 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE folder_role (folder_id INT NOT NULL, role_id INT NOT NULL, INDEX IDX_4EF6AC7F162CB942 (folder_id), INDEX IDX_4EF6AC7FD60322AC (role_id), PRIMARY KEY(folder_id, role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE folder_role ADD CONSTRAINT FK_4EF6AC7F162CB942 FOREIGN KEY (folder_id) REFERENCES folder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folder_role ADD CONSTRAINT FK_4EF6AC7FD60322AC FOREIGN KEY (role_id) REFERENCES role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folder ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE folder ADD CONSTRAINT FK_ECA209CD7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_ECA209CD7E3C61F9 ON folder (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE folder_role DROP FOREIGN KEY FK_4EF6AC7F162CB942');
        $this->addSql('ALTER TABLE folder_role DROP FOREIGN KEY FK_4EF6AC7FD60322AC');
        $this->addSql('DROP TABLE folder_role');
        $this->addSql('ALTER TABLE folder DROP FOREIGN KEY FK_ECA209CD7E3C61F9');
        $this->addSql('DROP INDEX IDX_ECA209CD7E3C61F9 ON folder');
        $this->addSql('ALTER TABLE folder DROP owner_id');
    }
}
