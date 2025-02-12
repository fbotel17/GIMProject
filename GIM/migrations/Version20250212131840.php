<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250212131840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE traitement_medicament (traitement_id INT NOT NULL, medicament_id INT NOT NULL, INDEX IDX_7E796CD5DDA344B6 (traitement_id), INDEX IDX_7E796CD5AB0D61F7 (medicament_id), PRIMARY KEY(traitement_id, medicament_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE traitement_medicament ADD CONSTRAINT FK_7E796CD5DDA344B6 FOREIGN KEY (traitement_id) REFERENCES traitement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE traitement_medicament ADD CONSTRAINT FK_7E796CD5AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE traitement DROP FOREIGN KEY FK_2A356D27AB0D61F7');
        $this->addSql('DROP INDEX IDX_2A356D27AB0D61F7 ON traitement');
        $this->addSql('ALTER TABLE traitement DROP medicament_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE traitement_medicament DROP FOREIGN KEY FK_7E796CD5DDA344B6');
        $this->addSql('ALTER TABLE traitement_medicament DROP FOREIGN KEY FK_7E796CD5AB0D61F7');
        $this->addSql('DROP TABLE traitement_medicament');
        $this->addSql('ALTER TABLE traitement ADD medicament_id INT NOT NULL');
        $this->addSql('ALTER TABLE traitement ADD CONSTRAINT FK_2A356D27AB0D61F7 FOREIGN KEY (medicament_id) REFERENCES medicament (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_2A356D27AB0D61F7 ON traitement (medicament_id)');
    }
}
