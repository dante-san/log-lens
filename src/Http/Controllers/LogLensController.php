<?php

namespace Laxmidhar\LogLens\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\LazyCollection;

class LogLensController extends Controller
{
    private int $chunkSize;
    private int $maxEntries;

    public function __construct()
    {
        $this->chunkSize  = config('loglens.chunk_size', 8192);
        $this->maxEntries = config('loglens.max_entries', 500);
    }

    public function index(Request $request)
    {
        $logsPath = storage_path('logs');

        if (!File::exists($logsPath)) {
            File::makeDirectory($logsPath, 0755, true);
        }

        $logFiles = $this->getLogFiles($logsPath);

        if (empty($logFiles)) {
            File::put($logsPath . '/laravel.log', '');
            $logFiles = [$logsPath . '/laravel.log'];
        }

        $currentFile = $request->get('file');
        if ($currentFile) {
            $currentFile = $logsPath . '/' . basename($currentFile);
            if (!in_array($currentFile, $logFiles)) {
                $currentFile = $logFiles[0];
            }
        } else {
            $currentFile = $logFiles[0];
        }

        $fileSizes = [];
        foreach ($logFiles as $file) {
            $fileSizes[$file] = $this->formatBytes(File::size($file));
        }

        $page    = (int) $request->get('page', 1);
        $result  = $this->parseLogFileLazy($currentFile, $page);
        $stats   = $this->calculateStats($currentFile);

        return view('loglens::index', [
            'logFiles'    => $logFiles,
            'currentFile' => $currentFile,
            'fileSizes'   => $fileSizes,
            'logEntries'  => $result['entries'],
            'stats'       => $stats,
            'totalPages'  => $result['total_pages'],
            'currentPage' => $page,
            'totalEntries' => $result['total'] ?? 0,
        ]);
    }

    public function clear(Request $request, $file = null)
    {
        $logsPath = storage_path('logs');
        $filePath = $logsPath . '/' . basename($file ?? 'laravel.log');

        if (File::exists($filePath)) {
            File::put($filePath, '');
            return redirect()->route('loglens.index', ['file' => basename($filePath)])
                ->with('success', 'Log file cleared successfully!');
        }

        return redirect()->route('loglens.index')->with('error', 'Log file not found!');
    }

    public function download($file = null)
    {
        $logsPath = storage_path('logs');
        $filePath = $logsPath . '/' . basename($file ?? 'laravel.log');

        if (File::exists($filePath)) {
            return response()->download($filePath);
        }

        return redirect()->route('loglens.index')->with('error', 'Log file not found!');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'logFile' => 'required|file|mimes:log,txt',
        ]);

        $file     = $request->file('logFile');
        $filename = $file->getClientOriginalName();
        $file->move(storage_path('logs'), $filename);

        return response()->json([
            'success'  => true,
            'filename' => $filename,
            'message'  => "Log file '{$filename}' uploaded successfully!"
        ]);
    }

    private function getLogFiles(string $path): array
    {
        $files = File::glob($path . '/*.log');
        usort($files, fn($a, $b) => File::lastModified($b) - File::lastModified($a));
        return $files;
    }

    private function parseLogFileLazy(string $filePath, int $page = 1): array
    {
        if (!File::exists($filePath) || File::size($filePath) === 0) {
            return ['entries' => [], 'total' => 0, 'total_pages' => 1];
        }

        $pattern = '/^\[(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\](?:\s+\w+\.)?(\w+):\s+(.+)$/';
        $entries = [];
        $current = null;

        $lines = LazyCollection::make(function () use ($filePath) {
            $handle = fopen($filePath, 'r');
            while (!feof($handle)) {
                yield fgets($handle);
            }
            fclose($handle);
        });

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            if (preg_match($pattern, $line, $matches)) {
                if ($current) $entries[] = $current;
                $current = [
                    'timestamp' => $matches[1],
                    'level'     => strtoupper($matches[2]),
                    'message'   => $matches[3],
                    'context'   => '',
                ];
            } elseif ($current) {
                $current['context'] .= $line . "\n";
            }
        }

        if ($current) $entries[] = $current;

        $entries    = array_reverse($entries);
        $total      = count($entries);
        // $totalPages = max(1, ceil($total / $this->maxEntries));
        // $entries    = array_slice($entries, ($page - 1) * $this->maxEntries, $this->maxEntries);

        return ['entries' => $entries, 'total' => $total, 'total_pages' => 1];
    }

    private function calculateStats(string $filePath): array
    {
        $stats   = ['error' => 0, 'warning' => 0, 'info' => 0, 'debug' => 0];
        $pattern = '/^\[[\d\s:-]+\](?:\s+\w+\.)?(\w+):/';

        $handle = fopen($filePath, 'r');
        while (!feof($handle)) {
            $line = fgets($handle);
            if (preg_match($pattern, $line, $matches)) {
                $level = strtolower($matches[1]);
                if (isset($stats[$level])) $stats[$level]++;
            }
        }
        fclose($handle);

        return $stats;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
