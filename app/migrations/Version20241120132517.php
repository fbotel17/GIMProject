<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241120132517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE medicament CHANGE nom nom VARCHAR(500) NOT NULL, CHANGE forme_pharmaceutique forme_pharmaceutique VARCHAR(500) NOT NULL, CHANGE voie_administration voie_administration VARCHAR(500) DEFAULT NULL, CHANGE etat_autorisation etat_autorisation VARCHAR(500) NOT NULL, CHANGE etat_commercialisation etat_commercialisation VARCHAR(500) NOT NULL, CHANGE fabricant fabricant VARCHAR(500) DEFAULT NULL, CHANGE `procedure` `procedure` VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE medicament CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE forme_pharmaceutique forme_pharmaceutique VARCHAR(255) NOT NULL, CHANGE voie_administration voie_administration VARCHAR(255) NOT NULL, CHANGE etat_autorisation etat_autorisation VARCHAR(255) NOT NULL, CHANGE `procedure` `procedure` VARCHAR(255) NOT NULL, CHANGE etat_commercialisation etat_commercialisation VARCHAR(255) NOT NULL, CHANGE fabricant fabricant VARCHAR(255) DEFAULT NULL');
    }
}
