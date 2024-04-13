<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231105201645 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
		$this->addSql("INSERT INTO role (name, code) VALUES ('ROLE_USER', 'ROLE_USER')");
		$this->addSql("INSERT INTO role (name, code) VALUES ('ROLE_ADMIN', 'ROLE_ADMIN')");
		$this->addSql("INSERT INTO role (name, code) VALUES ('ROLE_SUPER_ADMIN', 'ROLE_SUPER_ADMIN')");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
