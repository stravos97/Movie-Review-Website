<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201122212608 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE director (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(50) NOT NULL, last_name VARCHAR(100) NOT NULL, biography LONGTEXT DEFAULT NULL, image_file VARCHAR(255) NOT NULL, image_name VARCHAR(255) DEFAULT NULL, updated_at DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE film (id INT AUTO_INCREMENT NOT NULL, director_id INT DEFAULT NULL, title VARCHAR(255) NOT NULL, synopsis LONGTEXT DEFAULT NULL, genre VARCHAR(100) NOT NULL, published_date VARCHAR(255) NOT NULL, image_file VARCHAR(255) DEFAULT NULL, image_name VARCHAR(60) DEFAULT NULL, updated_at DATETIME NOT NULL, INDEX IDX_8244BE22899FB366 (director_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE review (id INT AUTO_INCREMENT NOT NULL, film_id INT NOT NULL, rating INT DEFAULT NULL, date DATE DEFAULT NULL COMMENT \'(DC2Type:date_immutable)\', summary VARCHAR(255) NOT NULL, message_body LONGTEXT NOT NULL, reported TINYINT(1) DEFAULT NULL, INDEX IDX_794381C6567F5183 (film_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE film ADD CONSTRAINT FK_8244BE22899FB366 FOREIGN KEY (director_id) REFERENCES director (id)');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6567F5183 FOREIGN KEY (film_id) REFERENCES film (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE film DROP FOREIGN KEY FK_8244BE22899FB366');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6567F5183');
        $this->addSql('DROP TABLE director');
        $this->addSql('DROP TABLE film');
        $this->addSql('DROP TABLE review');
    }
}
