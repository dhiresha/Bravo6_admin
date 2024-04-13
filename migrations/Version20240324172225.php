<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240324172225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE folder_media (folder_id INT NOT NULL, media_id INT NOT NULL, INDEX IDX_7E8DAC1162CB942 (folder_id), INDEX IDX_7E8DAC1EA9FDD75 (media_id), PRIMARY KEY(folder_id, media_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE folder_media ADD CONSTRAINT FK_7E8DAC1162CB942 FOREIGN KEY (folder_id) REFERENCES folder (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE folder_media ADD CONSTRAINT FK_7E8DAC1EA9FDD75 FOREIGN KEY (media_id) REFERENCES media (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE folder_media DROP FOREIGN KEY FK_7E8DAC1162CB942');
        $this->addSql('ALTER TABLE folder_media DROP FOREIGN KEY FK_7E8DAC1EA9FDD75');
        $this->addSql('DROP TABLE folder_media');
    }
}
