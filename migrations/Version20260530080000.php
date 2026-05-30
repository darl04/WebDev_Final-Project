<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Fixes Doctrine "No identity value was generated" when inserting stock_adjustment rows.
 * The table may exist without AUTO_INCREMENT if it was created before migrations ran correctly.
 */
final class Version20260530080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ensure stock and stock_adjustment id columns use AUTO_INCREMENT';
    }

    public function up(Schema $schema): void
    {
        if ($this->tableExists('stock')) {
            $this->addSql('ALTER TABLE stock MODIFY id INT AUTO_INCREMENT NOT NULL');
        }
        if ($this->tableExists('stock_adjustment')) {
            $this->addSql('ALTER TABLE stock_adjustment MODIFY id INT AUTO_INCREMENT NOT NULL');
        }
        if ($this->tableExists('activity_log')) {
            $this->addSql('ALTER TABLE activity_log MODIFY id INT AUTO_INCREMENT NOT NULL');
        }
    }

    public function down(Schema $schema): void
    {
        // No safe rollback: removing AUTO_INCREMENT would break inserts again.
    }

    private function tableExists(string $tableName): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$tableName]
        );
    }
}
