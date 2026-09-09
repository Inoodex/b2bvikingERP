<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\BackupDataTable;
use App\Http\Controllers\Controller;
use App\Models\SystemBackupLog;
use App\Services\Maintenance\BackupService;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->backupService = $backupService;
    }

    /**
     * Display Database Backup & Disaster Recovery Center with Yajra DataTable.
     */
    public function index(BackupDataTable $dataTable)
    {
        return $dataTable->render('backend.maintenance.backups');
    }

    /**
     * Trigger an on-demand full database snapshot.
     */
    public function create()
    {
        try {
            $log = $this->backupService->createDatabaseBackup(auth()->id());
            $sizeMb = round($log->file_size_bytes / (1024 * 1024), 2);
            Toastr::success("Database backup '{$log->file_name}' ({$sizeMb} MB) generated successfully!");
        } catch (Exception $e) {
            Toastr::error("Backup generation failed: " . $e->getMessage());
        }

        return redirect()->route('admin.backups.index');
    }

    /**
     * Securely download backup SQL dump.
     */
    public function download(int $id)
    {
        $backup = SystemBackupLog::findOrFail($id);
        $fullPath = storage_path('app/' . $backup->file_path);

        if (!File::exists($fullPath)) {
            Toastr::error("Backup file not found on disk.");
            return redirect()->route('admin.backups.index');
        }

        return response()->download($fullPath, $backup->file_name, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Purge backup from disk and registry.
     */
    public function destroy(int $id)
    {
        $backup = SystemBackupLog::findOrFail($id);
        $this->backupService->deleteBackup($backup);

        Toastr::success("Backup file deleted successfully.");
        return redirect()->route('admin.backups.index');
    }
}
