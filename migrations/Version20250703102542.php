<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250703102542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE846E2F5C
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_E00CEDDE846E2F5C ON booking
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP aircraft_id, DROP origin, DROP destination, CHANGE flight_id flight_id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE91F478C5 FOREIGN KEY (flight_id) REFERENCES flight (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDE91F478C5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD aircraft_id INT DEFAULT NULL, ADD origin VARCHAR(255) NOT NULL, ADD destination VARCHAR(255) NOT NULL, CHANGE flight_id flight_id INT DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDE846E2F5C FOREIGN KEY (aircraft_id) REFERENCES aircraft (id) ON UPDATE NO ACTION ON DELETE NO ACTION
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E00CEDDE846E2F5C ON booking (aircraft_id)
        SQL);
    }
}
