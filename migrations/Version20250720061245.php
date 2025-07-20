<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250720061245 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE aircraft (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, model VARCHAR(255) NOT NULL, capacity INT NOT NULL, range_km INT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', image VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE booking (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, flight_id INT NOT NULL, status VARCHAR(20) NOT NULL, notes LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', seat_class VARCHAR(20) NOT NULL, passenger_count INT NOT NULL, mobile_number VARCHAR(25) DEFAULT NULL, INDEX IDX_E00CEDDEA76ED395 (user_id), INDEX IDX_E00CEDDE91F478C5 (flight_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE flight (id INT AUTO_INCREMENT NOT NULL, route_id INT NOT NULL, aircraft_id INT NOT NULL, departure_time DATETIME NOT NULL, arrival_time DATETIME NOT NULL, status VARCHAR(255) NOT NULL, available_seats INT NOT NULL, INDEX IDX_C257E60E34ECB4E6 (route_id), INDEX IDX_C257E60E846E2F5C (aircraft_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE passenger (id INT AUTO_INCREMENT NOT NULL, booking_id INT NOT NULL, name VARCHAR(100) NOT NULL, age INT NOT NULL, seat_number VARCHAR(10) DEFAULT NULL, document_id VARCHAR(50) DEFAULT NULL, check_in_code VARCHAR(25) DEFAULT NULL, is_checked_in TINYINT(1) NOT NULL, INDEX IDX_3BEFE8DD3301C60 (booking_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token VARCHAR(255) NOT NULL, expires_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_C74F21955F37A13B (token), INDEX IDX_C74F2195A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE routes (id INT AUTO_INCREMENT NOT NULL, aircraft_id INT DEFAULT NULL, origin VARCHAR(255) NOT NULL, destination VARCHAR(255) NOT NULL, INDEX IDX_32D5C2B3846E2F5C (aircraft_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE91F478C5 FOREIGN KEY (flight_id) REFERENCES flight (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flight ADD CONSTRAINT FK_C257E60E34ECB4E6 FOREIGN KEY (route_id) REFERENCES routes (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flight ADD CONSTRAINT FK_C257E60E846E2F5C FOREIGN KEY (aircraft_id) REFERENCES aircraft (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE passenger ADD CONSTRAINT FK_3BEFE8DD3301C60 FOREIGN KEY (booking_id) REFERENCES booking (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE routes ADD CONSTRAINT FK_32D5C2B3846E2F5C FOREIGN KEY (aircraft_id) REFERENCES aircraft (id) ON DELETE SET NULL
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE91F478C5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flight DROP FOREIGN KEY FK_C257E60E34ECB4E6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE flight DROP FOREIGN KEY FK_C257E60E846E2F5C
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE passenger DROP FOREIGN KEY FK_3BEFE8DD3301C60
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE routes DROP FOREIGN KEY FK_32D5C2B3846E2F5C
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE aircraft
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE booking
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE flight
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE passenger
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE refresh_token
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE routes
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
    }
}
