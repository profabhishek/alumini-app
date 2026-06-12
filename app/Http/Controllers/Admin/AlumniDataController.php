<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlumniData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AlumniDataController extends Controller
{
    private const BATCH_SIZE  = 500;
    private const MAX_FILE_MB = 20;

    // ─────────────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $filters = $request->only([
            'q',
            'gender',
            'level_of_study',
            'course',
            'branch',
            'institute',
            'campus',
            'joining_from', 'joining_to',
            'grad_from',    'grad_to',
            'country',
            'city',
            'has_email',
            'employed',
            'created_from', 'created_to',
            'dob_from',     'dob_to',
            'company',
            'designation',
            'address_state',
            'address_country',
        ]);

        $q = trim($filters['q'] ?? '');

        $query = AlumniData::query();

        // Full-text search
        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name',         'like', "%{$q}%")
                    ->orWhere('email',       'like', "%{$q}%")
                    ->orWhere('phone',       'like', "%{$q}%")
                    ->orWhere('alumni_code', 'like', "%{$q}%")
                    ->orWhere('institute',   'like', "%{$q}%")
                    ->orWhere('course',      'like', "%{$q}%")
                    ->orWhere('current_company',      'like', "%{$q}%")
                    ->orWhere('current_designation',  'like', "%{$q}%")
                    ->orWhere('current_city',         'like', "%{$q}%");
            });
        }

        // Exact / range filters
        if (!empty($filters['gender']))        $query->where('gender',        $filters['gender']);
        if (!empty($filters['level_of_study'])) $query->where('level_of_study', $filters['level_of_study']);
        if (!empty($filters['course']))        $query->where('course',        $filters['course']);
        if (!empty($filters['branch']))        $query->where('branch',        $filters['branch']);
        if (!empty($filters['institute']))     $query->where('institute',     $filters['institute']);
        if (!empty($filters['campus']))        $query->where('campus',        $filters['campus']);
        if (!empty($filters['country']))       $query->where('current_country', $filters['country']);
        if (!empty($filters['city']))          $query->where('current_city',  'like', "%{$filters['city']}%");
        if (!empty($filters['company']))       $query->where('current_company', 'like', "%{$filters['company']}%");
        if (!empty($filters['designation']))   $query->where('current_designation', 'like', "%{$filters['designation']}%");
        if (!empty($filters['address_state'])) $query->where('address_state', 'like', "%{$filters['address_state']}%");
        if (!empty($filters['address_country'])) $query->where('address_country', $filters['address_country']);

        // Joining year range
        if (!empty($filters['joining_from'])) $query->where('joining_year', '>=', (int) $filters['joining_from']);
        if (!empty($filters['joining_to']))   $query->where('joining_year', '<=', (int) $filters['joining_to']);

        // Graduation year range
        if (!empty($filters['grad_from'])) $query->where('graduation_year', '>=', (int) $filters['grad_from']);
        if (!empty($filters['grad_to']))   $query->where('graduation_year', '<=', (int) $filters['grad_to']);

        // DOB range
        if (!empty($filters['dob_from'])) $query->whereDate('dob', '>=', $filters['dob_from']);
        if (!empty($filters['dob_to']))   $query->whereDate('dob', '<=', $filters['dob_to']);

        // Record created range
        if (!empty($filters['created_from'])) $query->where(function ($sub) use ($filters) {
            $sub->whereDate('record_created_at', '>=', $filters['created_from'])
                ->orWhereDate('created_at', '>=', $filters['created_from']);
        });
        if (!empty($filters['created_to'])) $query->where(function ($sub) use ($filters) {
            $sub->whereDate('record_created_at', '<=', $filters['created_to'])
                ->orWhereDate('created_at', '<=', $filters['created_to']);
        });

        // Has email
        if (isset($filters['has_email']) && $filters['has_email'] !== '') {
            $filters['has_email'] === '1'
                ? $query->whereNotNull('email')->where('email', '!=', '')
                : $query->where(fn($s) => $s->whereNull('email')->orWhere('email', ''));
        }

        // Employed
        if (isset($filters['employed']) && $filters['employed'] !== '') {
            $filters['employed'] === '1'
                ? $query->whereNotNull('current_company')->where('current_company', '!=', '')
                : $query->where(fn($s) => $s->whereNull('current_company')->orWhere('current_company', ''));
        }

        $rows = $query->orderByDesc('id')->paginate(50)->withQueryString();

        // ── Stats (always over full table) ────────────────────────────────
        $total     = AlumniData::count();
        $withEmail = AlumniData::whereNotNull('email')->where('email', '!=', '')->count();
        $withPhone = AlumniData::whereNotNull('phone')->where('phone', '!=', '')->count();
        $employed  = AlumniData::whereNotNull('current_company')->where('current_company', '!=', '')->count();
        $batchYears = AlumniData::whereNotNull('graduation_year')->distinct()->count('graduation_year');
        $countries  = AlumniData::whereNotNull('current_country')->distinct()->count('current_country');

        // ── Distinct option lists for dropdowns ───────────────────────────
        $levelOptions    = $this->distinctOptions('level_of_study');
        $courseOptions   = $this->distinctOptions('course');
        $branchOptions   = $this->distinctOptions('branch');
        $instituteOptions = $this->distinctOptions('institute');
        $campusOptions   = $this->distinctOptions('campus');
        $countryOptions  = $this->distinctOptions('current_country');
        $addressCountryOptions = $this->distinctOptions('address_country');
        $addressStateOptions   = $this->distinctOptions('address_state');

        return view('admin.alumni-data.index', compact(
            'rows', 'q', 'filters',
            'total', 'withEmail', 'withPhone', 'employed', 'batchYears', 'countries',
            'levelOptions', 'courseOptions', 'branchOptions', 'instituteOptions',
            'campusOptions', 'countryOptions', 'addressCountryOptions', 'addressStateOptions'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // IMPORT
    // ─────────────────────────────────────────────────────────────────────

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => [
                'required', 'file',
                'mimes:csv,txt,xlsx,xls',
                'max:' . (self::MAX_FILE_MB * 1024),
            ],
            'mode' => 'required|in:append,replace',
        ]);

        $file = $request->file('csv_file');
        $mode = $request->input('mode');

        try {
            $rows = $this->parseFile($file);
        } catch (\Exception $e) {
            return back()->with('import_error', 'Could not read file: ' . $e->getMessage());
        }

        if (empty($rows)) {
            return back()->with('import_error', 'File is empty or has no recognisable data rows.');
        }

        $map     = AlumniData::csvColumnMap();
        $headers = array_map(fn($h) => strtolower(trim($h)), array_keys($rows[0]));
        $cols    = [];

        foreach ($headers as $i => $h) {
            if (isset($map[$h])) $cols[$i] = $map[$h];
        }

        if (empty($cols)) {
            return back()->with('import_error', 'No matching columns found. Please use the provided template.');
        }

        if ($mode === 'replace') {
            AlumniData::truncate();
        }

        $inserted = 0;
        $skipped  = 0;
        $batch    = [];
        $now      = now();

        foreach ($rows as $row) {
            $values = array_values($row);
            $record = ['created_at' => $now, 'updated_at' => $now];

            foreach ($cols as $i => $dbCol) {
                $val = isset($values[$i]) ? trim((string) $values[$i]) : null;
                if (
                    $dbCol === 'email' &&
                    !empty($record['email'])
                ) {
                    continue;
                }

                if ($dbCol === 'email' && !empty($val)) {

                    $emails = preg_split('/\s*,\s*/', $val);

                    $val = trim($emails[0] ?? '');
                }

                $record[$dbCol] = $val === '' ? null : $val;
                
                if ($dbCol === 'phone' && !empty($val)) {
                    $numbers = preg_split('/\s*,\s*/', $val);
                    $numbers = array_map(function ($number) {
                        $number = trim($number);
                        if (preg_match('/e[\+\-]?\d+/i', $number)) {
                            $number = sprintf('%.0f', (float) $number);
                        }
                        return $number;
                    }, $numbers);
                    $val = implode(',', array_filter($numbers));
                }

                // Normalise gender
                if ($dbCol === 'gender' && !empty($val)) {
                    $val = strtolower($val);
                    if (!in_array($val, ['male', 'female', 'other'])) $val = 'other';
                }

                if ($dbCol === 'email' && !empty($val)) {

                    // Take only first email if multiple emails exist
                    $emails = preg_split('/\s*,\s*/', $val);

                    $val = trim($emails[0] ?? '');
                }
                $record[$dbCol] = $val === '' ? null : $val;
            }

            // Parse date / datetime fields
            foreach (['dob', 'record_created_at', 'record_updated_at'] as $dateCol) {
                if (!empty($record[$dateCol])) {
                    try {
                        $record[$dateCol] = Carbon::parse($record[$dateCol])->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $record[$dateCol] = null;
                    }
                }
            }

            // Normalise year fields
            foreach (['joining_year', 'graduation_year'] as $yearCol) {
                if (!empty($record[$yearCol])) {
                    $year = (int) $record[$yearCol];
                    $record[$yearCol] = ($year >= 1900 && $year <= 2100) ? $year : null;
                }
            }

            if (empty($record['name']) && empty($record['email']) && empty($record['alumni_code'])) {
                $skipped++;
                continue;
            }

            if (
                !empty($record['email']) &&
                strlen($record['email']) > 255
            ) {
                dd([
                    'email' => $record['email'],
                    'length' => strlen($record['email']),
                    'row' => $row,
                ]);
            }

            $batch[] = $record;

            if (count($batch) >= self::BATCH_SIZE) {
                DB::table('alumni_data')->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        }

        if (!empty($batch)) {
            DB::table('alumni_data')->insert($batch);
            $inserted += count($batch);
        }

        return back()->with('import_success', "Import complete. {$inserted} records inserted, {$skipped} skipped.");
    }

    // ─────────────────────────────────────────────────────────────────────
    // EXPORT
    // ─────────────────────────────────────────────────────────────────────

    public function export(Request $request)
    {
        $format  = $request->input('format', 'csv');
        $colsRaw = array_filter(explode(',', $request->input('columns', '')));

        // Validate requested columns against whitelist
        $allowed = array_keys(AlumniData::exportableColumns());
        $cols    = array_values(array_intersect($colsRaw, $allowed));

        if (empty($cols)) {
            $cols = AlumniData::defaultExportColumns();
        }

        // Re-use the same filter logic as index()
        $query = $this->buildFilteredQuery($request);
        $query->orderByDesc('id');

        if ($format === 'pdf') {
            return $this->exportPdf($query, $cols);
        }

        return $this->exportCsv($query, $cols);
    }

    private function exportCsv($query, array $cols)
    {
        $labels  = AlumniData::exportableColumns();
        $headers = array_map(fn($c) => $labels[$c] ?? $c, $cols);

        $filename = 'alumni_export_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($query, $cols, $headers) {
            $handle = fopen('php://output', 'w');
            // BOM for Excel UTF-8 compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            $query->chunk(500, function ($records) use ($handle, $cols) {
                foreach ($records as $record) {
                    $row = [];
                    foreach ($cols as $col) {
                        $val = $record->{$col} ?? '';
                        // Format dates nicely
                        if (in_array($col, ['dob', 'record_created_at', 'record_updated_at']) && $val) {
                            try {
                                $val = Carbon::parse($val)->format('Y-m-d');
                            } catch (\Exception $e) {}
                        }
                        $row[] = $val;
                    }
                    fputcsv($handle, $row);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control'       => 'no-cache, no-store',
        ]);
    }

    private function exportPdf($query, array $cols)
    {
        $labels  = AlumniData::exportableColumns();
        $headers = array_map(fn($c) => $labels[$c] ?? $c, $cols);
        $records = $query->limit(5000)->get(); // PDF hard cap

        $html = view('admin.alumni-data.export-pdf', compact('records', 'cols', 'headers', 'labels'))->render();

        // If Dompdf / Browsershot is available, use it; otherwise stream the HTML for browser print.
        if (class_exists('\Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => false]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();
            $filename = 'alumni_export_' . now()->format('Ymd_His') . '.pdf';
            return response($dompdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        // Fallback: return print-friendly HTML
        return response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }

    // ─────────────────────────────────────────────────────────────────────
    // DESTROY / CLEAR
    // ─────────────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        AlumniData::findOrFail($id)->delete();
        return back()->with('import_success', 'Record deleted.');
    }

    public function clearAll()
    {
        AlumniData::truncate();
        return back()->with('import_success', 'All alumni data records cleared.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // TEMPLATE DOWNLOAD
    // ─────────────────────────────────────────────────────────────────────

    public function template()
    {
        $headers = [
            'UserID', 'CQ: Alumni Code', 'Name', 'Email Address', 'Mobile Number',
            'Date of Birth', 'CQ: Gender', 'Profile Image Url', 'Linkedin Url',
            'Facebook Url', 'Current Company', 'Current Designation', 'Current City',
            'Current Country', 'CQ: Course Name', 'CQ: Branch Name', 'CQ: Campus Name',
            'CQ: Institute', 'CQ: Level of Study', 'CQ: Joining Year', 'CQ: Graduation Year',
            'CQ: Communication Address Line1', 'CQ: Communication Address Line2',
            'CQ: Communication Address City', 'CQ: Communication Address State',
            'CQ: Communication Address Country', 'CQ: Communication Address PinCode',
            'Registration Date', 'Last updated At',
        ];

        $csv = implode(',', array_map(fn($h) => '"' . $h . '"', $headers)) . "\n";
        $csv .= implode(',', array_fill(0, count($headers), '""')) . "\n";

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="alumni_data_template.csv"',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // PRIVATE HELPERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Build an Eloquent query from the request filters.
     * Shared between index() and export().
     */
    private function buildFilteredQuery(Request $request)
    {
        $q       = trim($request->input('q', ''));
        $filters = $request->all();
        $query   = AlumniData::query();

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('name',         'like', "%{$q}%")
                    ->orWhere('email',       'like', "%{$q}%")
                    ->orWhere('phone',       'like', "%{$q}%")
                    ->orWhere('alumni_code', 'like', "%{$q}%")
                    ->orWhere('institute',   'like', "%{$q}%")
                    ->orWhere('course',      'like', "%{$q}%")
                    ->orWhere('current_company',     'like', "%{$q}%")
                    ->orWhere('current_designation', 'like', "%{$q}%")
                    ->orWhere('current_city',        'like', "%{$q}%");
            });
        }

        $exact = ['gender', 'level_of_study', 'course', 'branch', 'institute', 'campus'];
        foreach ($exact as $field) {
            if (!empty($filters[$field])) $query->where($field, $filters[$field]);
        }

        if (!empty($filters['country']))         $query->where('current_country', $filters['country']);
        if (!empty($filters['city']))            $query->where('current_city', 'like', "%{$filters['city']}%");
        if (!empty($filters['company']))         $query->where('current_company', 'like', "%{$filters['company']}%");
        if (!empty($filters['designation']))     $query->where('current_designation', 'like', "%{$filters['designation']}%");
        if (!empty($filters['address_state']))   $query->where('address_state', 'like', "%{$filters['address_state']}%");
        if (!empty($filters['address_country'])) $query->where('address_country', $filters['address_country']);

        if (!empty($filters['joining_from'])) $query->where('joining_year', '>=', (int) $filters['joining_from']);
        if (!empty($filters['joining_to']))   $query->where('joining_year', '<=', (int) $filters['joining_to']);
        if (!empty($filters['grad_from']))    $query->where('graduation_year', '>=', (int) $filters['grad_from']);
        if (!empty($filters['grad_to']))      $query->where('graduation_year', '<=', (int) $filters['grad_to']);
        if (!empty($filters['dob_from']))     $query->whereDate('dob', '>=', $filters['dob_from']);
        if (!empty($filters['dob_to']))       $query->whereDate('dob', '<=', $filters['dob_to']);
        if (!empty($filters['created_from'])) $query->whereDate('record_created_at', '>=', $filters['created_from']);
        if (!empty($filters['created_to']))   $query->whereDate('record_created_at', '<=', $filters['created_to']);

        if (isset($filters['has_email']) && $filters['has_email'] !== '') {
            $filters['has_email'] === '1'
                ? $query->whereNotNull('email')->where('email', '!=', '')
                : $query->where(fn($s) => $s->whereNull('email')->orWhere('email', ''));
        }

        if (isset($filters['employed']) && $filters['employed'] !== '') {
            $filters['employed'] === '1'
                ? $query->whereNotNull('current_company')->where('current_company', '!=', '')
                : $query->where(fn($s) => $s->whereNull('current_company')->orWhere('current_company', ''));
        }

        return $query;
    }

    private function distinctOptions(string $column): \Illuminate\Support\Collection
    {
        return AlumniData::whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column);
    }

    // ─────────────────────────────────────────────────────────────────────
    // FILE PARSERS
    // ─────────────────────────────────────────────────────────────────────

    private function parseFile($file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        return in_array($ext, ['xlsx', 'xls'])
            ? $this->parseXlsx($file->getRealPath())
            : $this->parseCsv($file->getRealPath());
    }

    private function parseCsv(string $path): array
    {
        $rows    = [];
        $headers = null;

        if (($handle = fopen($path, 'r')) === false) {
            throw new \Exception('Cannot open file.');
        }

        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") rewind($handle);

        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            if ($headers === null) {
                $headers = $line;
                continue;
            }
            if (count($line) === count($headers)) {
                $rows[] = array_combine($headers, $line);
            }
        }

        fclose($handle);
        return $rows;
    }

    private function parseXlsx(string $path): array
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new \Exception('XLSX support requires PhpSpreadsheet.');
        }

        $spreadsheet   = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $sheet         = $spreadsheet->getActiveSheet();
        $highestRow    = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $headers       = [];
        $rows          = [];

        for ($col = 'A'; $col <= $highestColumn; $col++) {
            $headers[] = trim((string) $sheet->getCell($col . '1')->getFormattedValue());
        }

        for ($row = 2; $row <= $highestRow; $row++) {
            $line = [];
            foreach (range('A', $highestColumn) as $col) {
                $line[] = $sheet->getCell($col . $row)->getFormattedValue();
            }
            $rows[] = array_combine($headers, $line);
        }

        return $rows;
    }
}