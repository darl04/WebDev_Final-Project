<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260510000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create stock adjustment history table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE IF NOT EXISTS user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, avatar VARCHAR(255) DEFAULT NULL, email VARCHAR(255) NOT NULL, is_verified TINYINT NOT NULL, verification_token VARCHAR(255) DEFAULT NULL, is_active TINYINT NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS products (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, price NUMERIC(10, 0) NOT NULL, description VARCHAR(255) DEFAULT NULL, created_at DATETIME DEFAULT NULL, image VARCHAR(255) DEFAULT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_B3BA5A5AB03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS stock (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, quantity INT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME DEFAULT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_4B3656604584665A (product_id), INDEX IDX_4B365660B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        if (!$this->hasForeignKey('products', 'FK_B3BA5A5AB03A8386')) {
            $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        }
        if (!$this->hasForeignKey('stock', 'FK_4B3656604584665A')) {
            $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        }
        if (!$this->hasForeignKey('stock', 'FK_4B365660B03A8386')) {
            $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        }
        $this->addSql('CREATE TABLE IF NOT EXISTS stock_adjustment (id INT AUTO_INCREMENT NOT NULL, stock_id INT NOT NULL, added_by_id INT DEFAULT NULL, quantity_added INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_3B6A4A7E6DAF1A8 (stock_id), INDEX IDX_3B6A4A7E72CA4172 (added_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        if (!$this->hasForeignKey('stock_adjustment', 'FK_3B6A4A7E6DAF1A8')) {
            $this->addSql('ALTER TABLE stock_adjustment ADD CONSTRAINT FK_3B6A4A7E6DAF1A8 FOREIGN KEY (stock_id) REFERENCES stock (id) ON DELETE CASCADE');
        }
        if (!$this->hasForeignKey('stock_adjustment', 'FK_3B6A4A7E72CA4172')) {
            $this->addSql('ALTER TABLE stock_adjustment ADD CONSTRAINT FK_3B6A4A7E72CA4172 FOREIGN KEY (added_by_id) REFERENCES user (id) ON DELETE SET NULL');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE stock_adjustment');
    }

    private function hasForeignKey(string $tableName, string $constraintName): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.table_constraints WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = "FOREIGN KEY"',
            [$tableName, $constraintName]
        );
    }
}