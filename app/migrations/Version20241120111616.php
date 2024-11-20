<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241120111616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inventaire (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, medicament_id INT NOT NULL, quantite INT NOT NULL, INDEX IDX_338920E0A76ED395 (user_id), INDEX IDX_338920E0AB0D61F7 (medicament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medicament (id INT AUTO_INCREMENT NOT NULL, code_cis VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, forme_pharmaceutique VARCHAR(255) NOT NULL, voie_administration VARCHAR(255) DEFAULT NULL, etat_autorisation VARCHAR(255) NOT NULL, `procedure` VARCHAR(255) NOT NULL, etat_commercialisation VARCHAR(255) NOT NULL, date_commercialisation DATE NOT NULL, fabricant VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE traitement (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, medicament_id INT NOT NULL, date_renouvellement DATE DEFAULT NULL, dose INT DEFAULT NULL, frequence VARCHAR(255) DEFAULT NULL, actif TINYINT(1) NOT NULL, INDEX IDX_2A356D27A76ED395 (user_id), INDEX IDX_2A356D27AB0D61F7 (medicament_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, date_naissance DATE NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE inventaire ADD CONSTRAINT FK_338920E0A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE inventaire ADD CONSTRAINT FK_338920E0AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id)');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D27A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D27AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id)');
        $this->addSql('DROP TABLE chauffeur');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE available_at available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chauffeur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE inventaire DROP FOREIGN KEY FK_338920E0A76ED395');
        $this->addSql('ALTER TABLE inventaire DROP FOREIGN KEY FK_338920E0AB0D61F7');
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D27A76ED395');
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D27AB0D61F7');
        $this->addSql('DROP TABLE inventaire');
        $this->addSql('DROP TABLE medicament');
        $this->addSql('DROP TABLE traitement');
        $this->addSql('DROP TABLE `user`');
        $this->addSql('ALTER TABLE messenger_messages CHANGE created_at created_at DATETIME NOT NULL, CHANGE available_at available_at DATETIME NOT NULL, CHANGE delivered_at delivered_at DATETIME DEFAULT NULL');
    }
}
