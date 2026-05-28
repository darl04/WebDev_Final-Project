<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260528083726 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE IF NOT EXISTS activity_log (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, username VARCHAR(180) DEFAULT NULL, role VARCHAR(64) DEFAULT NULL, action VARCHAR(32) NOT NULL, target LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS customer (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone_number VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_81398E09B03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS orders (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, created_at DATETIME NOT NULL, status VARCHAR(50) NOT NULL, customer_id INT NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_E52FFDEE9395C3F3 (customer_id), INDEX IDX_E52FFDEEB03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS orders_products (orders_id INT NOT NULL, products_id INT NOT NULL, INDEX IDX_749C879CCFFE9AD6 (orders_id), INDEX IDX_749C879C6C8A81A9 (products_id), PRIMARY KEY (orders_id, products_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS rental (id INT AUTO_INCREMENT NOT NULL, rental_price NUMERIC(10, 2) DEFAULT NULL, rental_date DATE DEFAULT NULL, return_date DATE DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, customer_id INT NOT NULL, created_by_id INT DEFAULT NULL, INDEX IDX_1619C27D9395C3F3 (customer_id), INDEX IDX_1619C27DB03A8386 (created_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS rental_products (rental_id INT NOT NULL, products_id INT NOT NULL, INDEX IDX_83DC4369A7CF2329 (rental_id), INDEX IDX_83DC43696C8A81A9 (products_id), PRIMARY KEY (rental_id, products_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE IF NOT EXISTS messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        if (!$this->hasForeignKey('customer', 'FK_81398E09B03A8386')) {
            $this->addSql('ALTER TABLE customer ADD CONSTRAINT FK_81398E09B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        }
        if (!$this->hasForeignKey('orders', 'FK_E52FFDEE9395C3F3')) {
            $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEE9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        }
        if (!$this->hasForeignKey('orders', 'FK_E52FFDEEB03A8386')) {
            $this->addSql('ALTER TABLE orders ADD CONSTRAINT FK_E52FFDEEB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        }
        if (!$this->hasForeignKey('orders_products', 'FK_749C879CCFFE9AD6')) {
            $this->addSql('ALTER TABLE orders_products ADD CONSTRAINT FK_749C879CCFFE9AD6 FOREIGN KEY (orders_id) REFERENCES orders (id) ON DELETE CASCADE');
        }
        if (!$this->hasForeignKey('orders_products', 'FK_749C879C6C8A81A9')) {
            $this->addSql('ALTER TABLE orders_products ADD CONSTRAINT FK_749C879C6C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE');
        }
        if (!$this->hasForeignKey('products', 'FK_B3BA5A5AB03A8386')) {
            $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5AB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        }
        if (!$this->hasForeignKey('rental', 'FK_1619C27D9395C3F3')) {
            $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27D9395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id)');
        }
        if (!$this->hasForeignKey('rental', 'FK_1619C27DB03A8386')) {
            $this->addSql('ALTER TABLE rental ADD CONSTRAINT FK_1619C27DB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        }
        if (!$this->hasForeignKey('rental_products', 'FK_83DC4369A7CF2329')) {
            $this->addSql('ALTER TABLE rental_products ADD CONSTRAINT FK_83DC4369A7CF2329 FOREIGN KEY (rental_id) REFERENCES rental (id) ON DELETE CASCADE');
        }
        if (!$this->hasForeignKey('rental_products', 'FK_83DC43696C8A81A9')) {
            $this->addSql('ALTER TABLE rental_products ADD CONSTRAINT FK_83DC43696C8A81A9 FOREIGN KEY (products_id) REFERENCES products (id) ON DELETE CASCADE');
        }
        if (!$this->hasForeignKey('stock', 'FK_4B3656604584665A')) {
            $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604584665A FOREIGN KEY (product_id) REFERENCES products (id)');
        }
        if (!$this->hasForeignKey('stock', 'FK_4B365660B03A8386')) {
            $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B365660B03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        }
        if (!$this->hasForeignKey('stock_adjustment', 'FK_27B08FBADCD6110')) {
            $this->addSql('ALTER TABLE stock_adjustment ADD CONSTRAINT FK_27B08FBADCD6110 FOREIGN KEY (stock_id) REFERENCES stock (id) ON DELETE CASCADE');
        }
        if (!$this->hasForeignKey('stock_adjustment', 'FK_27B08FBA55B127A4')) {
            $this->addSql('ALTER TABLE stock_adjustment ADD CONSTRAINT FK_27B08FBA55B127A4 FOREIGN KEY (added_by_id) REFERENCES user (id) ON DELETE SET NULL');
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP FOREIGN KEY FK_81398E09B03A8386');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEE9395C3F3');
        $this->addSql('ALTER TABLE orders DROP FOREIGN KEY FK_E52FFDEEB03A8386');
        $this->addSql('ALTER TABLE orders_products DROP FOREIGN KEY FK_749C879CCFFE9AD6');
        $this->addSql('ALTER TABLE orders_products DROP FOREIGN KEY FK_749C879C6C8A81A9');
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5AB03A8386');
        $this->addSql('ALTER TABLE rental DROP FOREIGN KEY FK_1619C27D9395C3F3');
        $this->addSql('ALTER TABLE rental DROP FOREIGN KEY FK_1619C27DB03A8386');
        $this->addSql('ALTER TABLE rental_products DROP FOREIGN KEY FK_83DC4369A7CF2329');
        $this->addSql('ALTER TABLE rental_products DROP FOREIGN KEY FK_83DC43696C8A81A9');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656604584665A');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B365660B03A8386');
        $this->addSql('ALTER TABLE stock_adjustment DROP FOREIGN KEY FK_27B08FBADCD6110');
        $this->addSql('ALTER TABLE stock_adjustment DROP FOREIGN KEY FK_27B08FBA55B127A4');
        $this->addSql('DROP TABLE activity_log');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE orders');
        $this->addSql('DROP TABLE orders_products');
        $this->addSql('DROP TABLE rental');
        $this->addSql('DROP TABLE rental_products');
        $this->addSql('DROP TABLE messenger_messages');
    }

    private function hasForeignKey(string $tableName, string $constraintName): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.table_constraints WHERE constraint_schema = DATABASE() AND table_name = ? AND constraint_name = ? AND constraint_type = "FOREIGN KEY"',
            [$tableName, $constraintName]
        );
    }
}
