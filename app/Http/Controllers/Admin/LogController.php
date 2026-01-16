<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Controllers\Controller;

class LogController extends Controller
{
    public function index()
    {
        return view('admin.logs.index');
    }

    public function fetch(Request $request)
    {
        $level = strtoupper($request->get('level', ''));
        $page  = max(1, (int) $request->get('page', 1));
        $perPage = 50;

        $logFile = storage_path('logs/laravel.log');

        if (!is_readable($logFile)) {
            return response()->json([
                'data' => [],
                'total' => 0,
                'error' => 'Log file not readable'
            ], 200);
        }

        $lines = [];

        // ✅ SAFE + FALLBACK
        if (function_exists('exec')) {
            $file = escapeshellarg($logFile);
            exec("tail -n 2000 {$file}", $lines);
        }

        // ❗ exec() disabled OR returned nothing
        if (empty($lines)) {
            $lines = array_slice(file($logFile, FILE_IGNORE_NEW_LINES), -2000);
        }

        // Newest first
        $lines = array_reverse($lines);

        // Filter by log level
        if ($level) {
            $lines = array_values(array_filter($lines, function ($line) use ($level) {
                return str_contains($line, '.' . $level);
            }));
        }

        $total = count($lines);
        $data  = array_slice($lines, ($page - 1) * $perPage, $perPage);

        return response()->json([
            'data' => $data,
            'total' => $total,
            'page' => $page
        ]);
    }

}
