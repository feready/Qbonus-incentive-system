<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterFileVersion;
use App\Services\AuditLogger;
use App\Services\ReferenceWorkbookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use Illuminate\View\View;

class MasterFileController extends Controller
{
    /**
     * Halaman daftar dan riwayat Master File.
     */
    public function index(): View
    {
        $activeVersion = MasterFileVersion::query()
            ->with(['uploader', 'activator'])
            ->where('is_active', true)
            ->where('status', 'ACTIVE')
            ->first();

        $versions = MasterFileVersion::query()
            ->with(['uploader', 'activator'])
            ->latest('version_number')
            ->paginate(15);

        return view('admin.master-files.index', [
            'activeVersion' => $activeVersion,
            'versions' => $versions,
            'requiredSheets' => ReferenceWorkbookService::REQUIRED_SHEETS,
        ]);
    }

    /**
     * Upload dan validasi Master File baru.
     *
     * File valid menjadi PENDING.
     * File tidak valid menjadi REJECTED.
     */
    public function store(
        Request $request,
        ReferenceWorkbookService $reference,
        AuditLogger $auditLogger
    ) {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xls,xlsx,xlsm',
                'extensions:xls,xlsx,xlsm',
                'max:51200',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'file.required' => 'File Master Insentif wajib dipilih.',
            'file.mimes' => 'Format file harus XLS, XLSX, atau XLSM.',
            'file.extensions' => 'Ekstensi file tidak diperbolehkan.',
            'file.max' => 'Ukuran file maksimal 50 MB.',
        ]);

        $file = $request->file('file');

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $storedName = Str::uuid() . '.' . $extension;

        $filePath = $file->storeAs(
            'master-files',
            $storedName,
            'local'
        );

        if (!$filePath) {
            throw new RuntimeException(
                'Master File gagal disimpan.'
            );
        }

        $absolutePath = Storage::disk('local')->path($filePath);

        $validationStatus = 'INVALID';
        $status = 'REJECTED';

        $validationSummary = [
            'status' => 'INVALID',
            'message' => 'Validasi belum berhasil.',
        ];

        try {
            $validationSummary = $reference->validateFile(
                $absolutePath
            );

            $validationStatus = 'VALID';
            $status = 'PENDING';

        } catch (Throwable $e) {

            // Simpan riwayat file yang ditolak agar bisa diaudit.
            $validationSummary = [
                'status' => 'INVALID',
                'message' => $e->getMessage(),
            ];
        }

        $sha256 = hash_file('sha256', $absolutePath);

        $version = DB::transaction(function () use (
            $file,
            $filePath,
            $absolutePath,
            $sha256,
            $validationStatus,
            $validationSummary,
            $status,
            $validated
        ) {
            $latest = MasterFileVersion::query()
                ->orderByDesc('version_number')
                ->lockForUpdate()
                ->first();

            $nextVersion = ($latest?->version_number ?? 0) + 1;

            return MasterFileVersion::create([
                'version_number' => $nextVersion,

                'original_filename' => $file->getClientOriginalName(),

                'file_path' => $filePath,

                'file_size' => filesize($absolutePath),

                'file_sha256' => $sha256,

                'uploaded_by' => auth()->id(),

                'validation_status' => $validationStatus,

                'validation_summary' => $validationSummary,

                'status' => $status,

                'is_active' => false,

                'notes' => $validated['notes'] ?? null,
            ]);
        });

        $auditLogger->log(
            'MASTER_FILE_UPLOADED',
            'Super Admin mengunggah Master File versi '
                . $version->version_number . '.',
            auth()->user(),
            [
                'master_file_version_id' => $version->id,
                'version_number' => $version->version_number,
                'validation_status' => $validationStatus,
                'original_filename' => $version->original_filename,
            ]
        );

        if ($validationStatus === 'VALID') {
            return redirect()
                ->route('admin.master-files.index')
                ->with(
                    'success',
                    'Master File versi '
                        . $version->version_number
                        . ' berhasil divalidasi. File masih PENDING dan belum aktif.'
                );
        }

        return redirect()
            ->route('admin.master-files.index')
            ->with(
                'error',
                'Master File ditolak pada proses validasi. '
                    . ($validationSummary['message'] ?? '')
            );
    }

    /**
     * Aktivasi versi baru atau rollback ke versi sebelumnya.
     */
    public function activate(
        MasterFileVersion $masterFileVersion,
        ReferenceWorkbookService $reference,
        AuditLogger $auditLogger
    ) {
        if ($masterFileVersion->validation_status !== 'VALID') {
            return back()->with(
                'error',
                'Master File belum lolos validasi dan tidak dapat diaktifkan.'
            );
        }

        $disk = Storage::disk('local');

        if (!$disk->exists($masterFileVersion->file_path)) {
            return back()->with(
                'error',
                'File Master tidak ditemukan di penyimpanan.'
            );
        }

        $absolutePath = $disk->path(
            $masterFileVersion->file_path
        );

        $currentHash = hash_file('sha256', $absolutePath);

        if (
            $masterFileVersion->file_sha256 &&
            !hash_equals(
                $masterFileVersion->file_sha256,
                $currentHash
            )
        ) {
            return back()->with(
                'error',
                'Checksum file tidak sesuai. File mungkin telah berubah atau rusak.'
            );
        }

        try {
            // Validasi ulang file sebelum aktivasi.
            $validationSummary = $reference->validateFile(
                $absolutePath
            );

        } catch (Throwable $e) {
            return back()->with(
                'error',
                'Aktivasi dibatalkan: ' . $e->getMessage()
            );
        }

        DB::transaction(function () use (
            $masterFileVersion,
            $validationSummary
        ) {
            // Arsipkan semua versi yang sebelumnya aktif.
            $activeVersions = MasterFileVersion::query()
                ->where('is_active', true)
                ->lockForUpdate()
                ->get();

            foreach ($activeVersions as $oldVersion) {
                if ($oldVersion->id !== $masterFileVersion->id) {
                    $oldVersion->update([
                        'is_active' => false,
                        'status' => 'ARCHIVED',
                    ]);
                }
            }

            $masterFileVersion->refresh();

            // Aktifkan versi yang dipilih.
            $masterFileVersion->update([
                'status' => 'ACTIVE',
                'validation_status' => 'VALID',
                'validation_summary' => $validationSummary,
                'is_active' => true,
                'activated_by' => auth()->id(),
                'activated_at' => now(),
            ]);
        });

        $auditLogger->log(
            'MASTER_FILE_ACTIVATED',
            'Super Admin mengaktifkan Master File versi '
                . $masterFileVersion->version_number . '.',
            auth()->user(),
            [
                'master_file_version_id' => $masterFileVersion->id,
                'version_number' => $masterFileVersion->version_number,
                'previously_active_version' => true,
            ]
        );

        return redirect()
            ->route('admin.master-files.index')
            ->with(
                'success',
                'Master File versi '
                    . $masterFileVersion->version_number
                    . ' berhasil diaktifkan.'
            );
    }
}
