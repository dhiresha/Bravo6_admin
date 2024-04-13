<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231029141304 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
		$admin_password = '$2y$13$bgNFVMp8KQfdRGQKdWBjwug02GIMBDJiYEP2AKPPJo6hKJQlupWwC';
        // this up() migration is auto-generated, please modify it to your needs
		$this->addSql("INSERT INTO `user` (`id`, `email`, `roles`, `password`) VALUES(1, 'admin@lekipuno.mu', '[\"ROLE_USER\",\"ROLE_SUPER_ADMIN\",\"ROLE_ADMIN\",\"ROLE_MODERATOR\"]','$admin_password')");

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
