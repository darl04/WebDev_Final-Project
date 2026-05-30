<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:db:fix-auto-increment',
    description: 'Ensure id columns use AUTO_INCREMENT (fixes stock insert errors on MySQL)',
)]
final class FixDatabaseAutoIncrementCommand extends Command
{
    /** @var list<string> */
    private const TABLES = ['stock', 'stock_adjustment', 'activity_log'];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fixed = 0;

        $this->ensureStockAdjustmentTableExists();

        foreach (self::TABLES as $table) {
            if (!$this->tableExists($table)) {
                $io->warning(sprintf('Table "%s" does not exist — skipped.', $table));
                continue;
            }

            if (!$this->columnExists($table, 'id')) {
                $io->warning(sprintf('Table "%s" has no id column — skipped.', $table));
                continue;
            }

            $this->connection->executeStatement(
                sprintf('ALTER TABLE `%s` MODIFY `id` INT AUTO_INCREMENT NOT NULL', $table)
            );
            $io->writeln(sprintf('<info>Fixed AUTO_INCREMENT on %s.id</info>', $table));
            ++$fixed;
        }

        if ($fixed === 0) {
            $io->error('No tables were updated. Check DATABASE_URL and that tables exist.');
            return Command::FAILURE;
        }

        $io->success(sprintf('Updated AUTO_INCREMENT on %d table(s).', $fixed));

        return Command::SUCCESS;
    }

    private function ensureStockAdjustmentTableExists(): void
    {
        if ($this->tableExists('stock_adjustment')) {
            return;
        }

        $this->connection->executeStatement(
            'CREATE TABLE stock_adjustment (
                id INT AUTO_INCREMENT NOT NULL,
                stock_id INT NOT NULL,
                added_by_id INT DEFAULT NULL,
                quantity_added INT NOT NULL,
                created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
                INDEX IDX_27B08FBADCD6110 (stock_id),
                INDEX IDX_27B08FBA55B127A4 (added_by_id),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB'
        );
    }

    private function tableExists(string $table): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?',
            [$table]
        );
    }

    private function columnExists(string $table, string $column): bool
    {
        return (bool) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$table, $column]
        );
    }
}
