<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191128223411 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE source CHANGE main_element_selector main_element_selector VARCHAR(255) DEFAULT NULL, CHANGE image_selector image_selector VARCHAR(255) DEFAULT NULL, CHANGE title_selector title_selector VARCHAR(255) DEFAULT NULL, CHANGE description_selector description_selector VARCHAR(255) DEFAULT NULL, CHANGE audio_selector audio_selector VARCHAR(255) DEFAULT NULL, CHANGE audio_source_attribute audio_source_attribute VARCHAR(255) DEFAULT NULL, CHANGE publication_date_selector publication_date_selector VARCHAR(255) DEFAULT NULL, CHANGE image_source_attribute image_source_attribute VARCHAR(255) DEFAULT NULL, CHANGE source_type source_type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL, CHANGE full_name full_name VARCHAR(255) DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE podcast CHANGE source_id source_id INT DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL, CHANGE audio audio VARCHAR(255) DEFAULT NULL, CHANGE video video VARCHAR(255) DEFAULT NULL, CHANGE type type VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE subscriber ADD unsubscribe_token VARCHAR(255) DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE podcast CHANGE source_id source_id INT DEFAULT NULL, CHANGE image image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE audio audio VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE video video VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE type type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE source CHANGE main_element_selector main_element_selector VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE image_selector image_selector VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE title_selector title_selector VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE description_selector description_selector VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE audio_selector audio_selector VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE audio_source_attribute audio_source_attribute VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE publication_date_selector publication_date_selector VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE image_source_attribute image_source_attribute VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE source_type source_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE subscriber DROP unsubscribe_token, CHANGE confirmation_token confirmation_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_bin`, CHANGE full_name full_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`, CHANGE created_at created_at DATETIME DEFAULT \'NULL\', CHANGE confirmation_token confirmation_token VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'NULL\' COLLATE `utf8mb4_unicode_ci`');
    }
}
