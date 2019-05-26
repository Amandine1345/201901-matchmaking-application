<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190110103555 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player CHANGE mail mail VARCHAR(255) DEFAULT NULL');
        $this->addSql("INSERT INTO `status_event` VALUES (1,'En préparation',0,'secondary'),(2,'Inscription',1,'info'),(3,'Complet',2,'danger'),(4,'En cours',3,'success'),(5,'Terminé',4,'light')");
        $this->addSql("INSERT INTO `timer` VALUES (1,1,0,5,0)");
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE player CHANGE mail mail VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
    }
}
