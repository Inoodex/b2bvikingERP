<?php

namespace App\Services\Maintenance;

use App\Models\SystemBackupLog;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class BackupService
{
    /**
     * Directory inside storage/app where backups reside.
     */
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
        if (!File::isDirectory($this->backupDir)) {
            File::makeDirectory($this->backupDir, 0755, true, true);
        }
    }

    /**
     * Run a resilient, pure-PHP database backup compatible with Namecheap Shared Hosting.
     * Streams tables and row chunks without requiring exec() or mysqldump binary.
     *
     * @param int|null $triggeredBy User ID who initiated the backup
     * @return SystemBackupLog
     * @throws Exception
     */
    public function createDatabaseBackup(?int $triggeredBy = null): SystemBackupLog
    {
        $timestamp = date('Y-m-d_His');
        $fileName = "backup_db_{$timestamp}.sql";
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $fileName;

        // Create initial pending log
        $log = SystemBackupLog::create([
            'file_name'       => $fileName,
            'disk'            => 'local',
            'file_path'       => 'backups/' . $fileName,
            'file_size_bytes' => 0,
            'backup_type'     => 'database',
            'status'          => 'pending',
            'triggered_by'    => $triggeredBy,
        ]);

        $handle = fopen($filePath, 'w');
        if (!$handle) {
            $log->update(['status' => 'failed']);
            throw new Exception("Unable to open file for writing at {$filePath}");
        }

        try {
            $connection = DB::connection();
            $pdo = $connection->getPdo();
            $databaseName = $connection->getDatabaseName();

            // SQL Header
            fwrite($handle, "-- B2B Viking ERP Database Backup\n");
            fwrite($handle, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Target Database: `{$databaseName}`\n");
            fwrite($handle, "-- Compatible with Namecheap Shared Hosting & Local Laragon\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

            // Fetch list of base tables belonging specifically to current database (filters out foreign schemas and views)
            $rawTables = DB::select("SELECT TABLE_NAME FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = 'BASE TABLE'", [$databaseName]);
            $tables = array_map(fn($t) => $t->TABLE_NAME, $rawTables);

            foreach ($tables as $table) {
                // Table structure
                fwrite($handle, "\n-- --------------------------------------------------------\n");
                fwrite($handle, "-- Table structure for table `{$table}`\n");
                fwrite($handle, "-- --------------------------------------------------------\n\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

                $createTableStmt = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_NUM);
                if (!empty($createTableStmt[1])) {
                    fwrite($handle, $createTableStmt[1] . ";\n\n");
                }

                // Table data in streaming chunks
                fwrite($handle, "-- Dumping data for table `{$table}`\n");
                $rowCount = DB::table($table)->count();
                if ($rowCount > 0) {
                    $chunkSize = 500;
                    $offset = 0;

                    while ($offset < $rowCount) {
                        $rows = DB::table($table)->offset($offset)->limit($chunkSize)->get();
                        if ($rows->isEmpty()) {
                            break;
                        }

                        $insertLines = [];
                        foreach ($rows as $row) {
                            $values = [];
                            foreach ((array)$row as $val) {
                                if (is_null($val)) {
                                    $values[] = 'NULL';
                                } elseif (is_numeric($val)) {
                                    $values[] = $val;
                                } else {
                                    $values[] = $pdo->quote((string)$val);
                                }
                            }
                            $insertLines[] = "(" . implode(', ', $values) . ")";
                        }

                        if (!empty($insertLines)) {
                            fwrite($handle, "INSERT INTO `{$table}` VALUES \n" . implode(",\n", $insertLines) . ";\n");
                        }

                        $offset += $chunkSize;
                    }
                }
                fwrite($handle, "\n");
            }

            fwrite($handle, "\nSET FOREIGN_KEY_CHECKS=1;\n");
            fclose($handle);

            $fileSizeBytes = filesize($filePath) ?: 0;

            $log->update([
                'file_size_bytes' => $fileSizeBytes,
                'status'          => 'completed',
                'completed_at'    => now(),
            ]);

            return $log;
        } catch (\Throwable $e) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $log->update(['status' => 'failed']);
            Log::error("Backup creation error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete backup from storage and database.
     */
    public function deleteBackup(SystemBackupLog $backup): bool
    {
        $fullPath = storage_path('app/' . $backup->file_path);
        if (File::exists($fullPath)) {
            File::delete($fullPath);
        }

        return (bool)$backup->delete();
    }
}
