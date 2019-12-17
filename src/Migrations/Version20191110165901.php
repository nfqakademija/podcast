<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191110165901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE source ADD main_element_selector VARCHAR(255) DEFAULT NULL, ADD image_selector VARCHAR(255) DEFAULT NULL, ADD title_selector VARCHAR(255) DEFAULT NULL, ADD description_selector VARCHAR(255) DEFAULT NULL, ADD audio_selector VARCHAR(255) DEFAULT NULL, ADD audio_source_attribute VARCHAR(255) DEFAULT NULL, ADD publication_date_selector VARCHAR(255) DEFAULT NULL, ADD image_source_attribute VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
        $this->addSql('ALTER TABLE podcast CHANGE source_id source_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE description description LONGTEXT DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE audio audio VARCHAR(255) DEFAULT NULL, CHANGE video video VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE podcast CHANGE source_id source_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE image image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE audio audio VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE video video VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE source DROP main_element_selector, DROP image_selector, DROP title_selector, DROP description_selector, DROP audio_selector, DROP audio_source_attribute, DROP publication_date_selector, DROP image_source_attribute');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`');
    }
}
