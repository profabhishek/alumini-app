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
            'alumni_code',
            'user_type',
            'gender',
            'level_of_study',
            'course',
            'branch',
            'institute',
            'campus',
            'joining_from',   'joining_to',
            'grad_from',      'grad_to',
            'country',
            'city',
            'has_email',
            'has_phone',
            'has_linkedin',
            'employed',
            'created_from',   'created_to',
            'updated_from',   'updated_to',
            'reg_from',       'reg_to',
            'dob_from',       'dob_to',
            'company',
            'designation',
            'address_city',
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
        if (!empty($filters['alumni_code']))   $query->where('alumni_code', 'like', "%{$filters['alumni_code']}%");
        if (!empty($filters['user_type']))     $query->where('user_type', $filters['user_type']);
        if (!empty($filters['gender']))        $query->where('gender', $filters['gender']);
        if (!empty($filters['level_of_study'])) $query->where('level_of_study', $filters['level_of_study']);
        if (!empty($filters['course']))        $query->where('course', $filters['course']);
        if (!empty($filters['branch']))        $query->where('branch', $filters['branch']);
        if (!empty($filters['institute']))     $query->where('institute', $filters['institute']);
        if (!empty($filters['campus']))        $query->where('campus', $filters['campus']);
        if (!empty($filters['country']))       $query->where('current_country', $filters['country']);
        if (!empty($filters['city']))          $query->where('current_city', 'like', "%{$filters['city']}%");
        if (!empty($filters['company']))       $query->where('current_company', 'like', "%{$filters['company']}%");
        if (!empty($filters['designation']))   $query->where('current_designation', 'like', "%{$filters['designation']}%");
        if (!empty($filters['address_city']))  $query->where('address_city', 'like', "%{$filters['address_city']}%");
        if (!empty($filters['address_state'])) $query->where('address_state', 'like', "%{$filters['address_state']}%");
        if (!empty($filters['address_country'])) $query->where('address_country', $filters['address_country']);

        // Year ranges
        if (!empty($filters['joining_from'])) $query->where('joining_year', '>=', (int) $filters['joining_from']);
        if (!empty($filters['joining_to']))   $query->where('joining_year', '<=', (int) $filters['joining_to']);
        if (!empty($filters['grad_from']))    $query->where('graduation_year', '>=', (int) $filters['grad_from']);
        if (!empty($filters['grad_to']))      $query->where('graduation_year', '<=', (int) $filters['grad_to']);

        // Date ranges
        if (!empty($filters['dob_from']))     $query->whereDate('dob', '>=', $filters['dob_from']);
        if (!empty($filters['dob_to']))       $query->whereDate('dob', '<=', $filters['dob_to']);

        if (!empty($filters['reg_from']))     $query->whereDate('registration_date', '>=', $filters['reg_from']);
        if (!empty($filters['reg_to']))       $query->whereDate('registration_date', '<=', $filters['reg_to']);

        if (!empty($filters['updated_from'])) $query->whereDate('record_updated_at', '>=', $filters['updated_from']);
        if (!empty($filters['updated_to']))   $query->whereDate('record_updated_at', '<=', $filters['updated_to']);

        if (!empty($filters['created_from'])) $query->where(function ($sub) use ($filters) {
            $sub->whereDate('record_created_at', '>=', $filters['created_from'])
                ->orWhereDate('created_at', '>=', $filters['created_from']);
        });
        if (!empty($filters['created_to'])) $query->where(function ($sub) use ($filters) {
            $sub->whereDate('record_created_at', '<=', $filters['created_to'])
                ->orWhereDate('created_at', '<=', $filters['created_to']);
        });

        // Presence filters
        if (isset($filters['has_email']) && $filters['has_email'] !== '') {
            $filters['has_email'] === '1'
                ? $query->whereNotNull('email')->where('email', '!=', '')
                : $query->where(fn($s) => $s->whereNull('email')->orWhere('email', ''));
        }

        if (isset($filters['has_phone']) && $filters['has_phone'] !== '') {
            $filters['has_phone'] === '1'
                ? $query->whereNotNull('phone')->where('phone', '!=', '')
                : $query->where(fn($s) => $s->whereNull('phone')->orWhere('phone', ''));
        }

        if (isset($filters['has_linkedin']) && $filters['has_linkedin'] !== '') {
            $filters['has_linkedin'] === '1'
                ? $query->whereNotNull('linkedin_url')->where('linkedin_url', '!=', '')
                : $query->where(fn($s) => $s->whereNull('linkedin_url')->orWhere('linkedin_url', ''));
        }

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
        $userTypeOptions       = $this->distinctOptions('user_type');
        $levelOptions          = $this->distinctOptions('level_of_study');
        $courseOptions         = $this->distinctOptions('course');
        $branchOptions         = $this->distinctOptions('branch');
        $instituteOptions      = $this->distinctOptions('institute');
        $campusOptions         = $this->distinctOptions('campus');
        $countryOptions        = $this->distinctOptions('current_country');
        $addressCityOptions    = $this->distinctOptions('address_city');
        $addressCountryOptions = $this->distinctOptions('address_country');
        $addressStateOptions   = $this->distinctOptions('address_state');

        return view('admin.alumni-data.index', compact(
            'rows', 'q', 'filters',
            'total', 'withEmail', 'withPhone', 'employed', 'batchYears', 'countries',
            'userTypeOptions', 'levelOptions', 'courseOptions', 'branchOptions',
            'instituteOptions', 'campusOptions', 'countryOptions',
            'addressCityOptions', 'addressCountryOptions', 'addressStateOptions'
        ));
    }

    // ─────────────────────────────────────────────────────────────────────
    // IMPORT
    // ─────────────────────────────────────────────────────────────────────

    public function import(Request $request)
    {
        // Large files need more memory and time
        ini_set('memory_limit', '512M');
        set_time_limit(300);

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
        $ext  = strtolower($file->getClientOriginalExtension());

        $map      = AlumniData::csvColumnMap();
        $cols     = null;   // built from first row's headers
        $inserted = 0;
        $skipped  = 0;
        $batch    = [];
        $now      = now();

        if ($mode === 'replace') {
            AlumniData::truncate();
        }

        // Callback invoked for each data row (assoc array: header => value)
        $processRow = function (array $rowAssoc) use (
            $map, &$cols, &$batch, &$inserted, &$skipped, $now
        ) {
            // First call: build column index map from headers
            if ($cols === null) {
                $hdrs = array_map(fn($h) => strtolower(trim((string) $h)), array_keys($rowAssoc));
                $cols = [];
                foreach ($hdrs as $i => $h) {
                    if (isset($map[$h])) $cols[$i] = $map[$h];
                }
                if (empty($cols)) {
                    throw new \RuntimeException(
                        'No matching columns found. Check your file headers against the template.'
                    );
                }
            }

            $values = array_values($rowAssoc);
            $record = ['created_at' => $now, 'updated_at' => $now];

            foreach ($cols as $i => $dbCol) {
                $val = isset($values[$i]) ? trim((string) $values[$i]) : '';

                // Email: first address only; never overwrite if already set
                if ($dbCol === 'email') {
                    if (!empty($record['email'])) continue;
                    if ($val !== '') {
                        $parts = preg_split('/\s*,\s*/', $val);
                        $val   = trim($parts[0] ?? '');
                    }
                }

                // Phone: handle scientific notation (e.g. 9.19E+9), keep first number
                if ($dbCol === 'phone' && $val !== '') {
                    $parts = preg_split('/\s*,\s*/', $val);
                    $parts = array_map(function ($n) {
                        $n = trim($n);
                        return preg_match('/e[\+\-]?\d+/i', $n) ? sprintf('%.0f', (float) $n) : $n;
                    }, $parts);
                    $val = implode(',', array_filter($parts));
                }

                // Gender: normalise to lowercase, default 'other'
                if ($dbCol === 'gender' && $val !== '') {
                    $lower = strtolower($val);
                    $val   = in_array($lower, ['male', 'female', 'other']) ? $lower : 'other';
                }

                $record[$dbCol] = $val === '' ? null : $val;
            }

            // dob is a DATE column — store as Y-m-d only
            if (!empty($record['dob'])) {
                try {
                    $record['dob'] = Carbon::parse($record['dob'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $record['dob'] = null;
                }
            }

            // Datetime columns
            foreach (['record_created_at', 'record_updated_at', 'registration_date'] as $dc) {
                if (!empty($record[$dc])) {
                    try {
                        $record[$dc] = Carbon::parse($record[$dc])->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $record[$dc] = null;
                    }
                }
            }

            // Year fields: must be a plausible year integer
            foreach (['joining_year', 'graduation_year'] as $yc) {
                if (!empty($record[$yc])) {
                    $year = (int) $record[$yc];
                    $record[$yc] = ($year >= 1900 && $year <= 2100) ? $year : null;
                }
            }

            // Skip rows with no identifying information
            if (empty($record['name']) && empty($record['email']) && empty($record['alumni_code'])) {
                $skipped++;
                return;
            }

            // Skip malformed emails
            if (!empty($record['email']) && strlen($record['email']) > 255) {
                $skipped++;
                return;
            }

            $batch[] = $record;

            if (count($batch) >= self::BATCH_SIZE) {
                DB::table('alumni_data')->insert($batch);
                $inserted += count($batch);
                $batch = [];
            }
        };

        try {
            if (in_array($ext, ['xlsx', 'xls'])) {
                $this->streamXlsx($file->getRealPath(), $processRow);
            } else {
                $this->streamCsv($file->getRealPath(), $processRow);
            }
        } catch (\RuntimeException $e) {
            return back()->with('import_error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('import_error', 'Could not read file: ' . $e->getMessage());
        }

        if ($cols === null) {
            return back()->with('import_error', 'File is empty or has no recognisable data rows.');
        }

        // Flush remaining batch
        if (!empty($batch)) {
            DB::table('alumni_data')->insert($batch);
            $inserted += count($batch);
        }

        return back()->with('import_success',
            "Import complete — {$inserted} records inserted, {$skipped} skipped."
        );
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
                        if (in_array($col, ['dob', 'record_created_at', 'record_updated_at', 'registration_date']) && $val) {
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
            'UserID', 'CQ: Alumni Code', 'User Type', 'Name', 'Email Address', 'Mobile Number',
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

        if (!empty($filters['alumni_code']))   $query->where('alumni_code', 'like', "%{$filters['alumni_code']}%");
        if (!empty($filters['user_type']))     $query->where('user_type', $filters['user_type']);

        $exact = ['gender', 'level_of_study', 'course', 'branch', 'institute', 'campus'];
        foreach ($exact as $field) {
            if (!empty($filters[$field])) $query->where($field, $filters[$field]);
        }

        if (!empty($filters['country']))         $query->where('current_country', $filters['country']);
        if (!empty($filters['city']))            $query->where('current_city', 'like', "%{$filters['city']}%");
        if (!empty($filters['company']))         $query->where('current_company', 'like', "%{$filters['company']}%");
        if (!empty($filters['designation']))     $query->where('current_designation', 'like', "%{$filters['designation']}%");
        if (!empty($filters['address_city']))    $query->where('address_city', 'like', "%{$filters['address_city']}%");
        if (!empty($filters['address_state']))   $query->where('address_state', 'like', "%{$filters['address_state']}%");
        if (!empty($filters['address_country'])) $query->where('address_country', $filters['address_country']);

        if (!empty($filters['joining_from'])) $query->where('joining_year', '>=', (int) $filters['joining_from']);
        if (!empty($filters['joining_to']))   $query->where('joining_year', '<=', (int) $filters['joining_to']);
        if (!empty($filters['grad_from']))    $query->where('graduation_year', '>=', (int) $filters['grad_from']);
        if (!empty($filters['grad_to']))      $query->where('graduation_year', '<=', (int) $filters['grad_to']);
        if (!empty($filters['dob_from']))     $query->whereDate('dob', '>=', $filters['dob_from']);
        if (!empty($filters['dob_to']))       $query->whereDate('dob', '<=', $filters['dob_to']);

        if (!empty($filters['reg_from']))     $query->whereDate('registration_date', '>=', $filters['reg_from']);
        if (!empty($filters['reg_to']))       $query->whereDate('registration_date', '<=', $filters['reg_to']);

        if (!empty($filters['updated_from'])) $query->whereDate('record_updated_at', '>=', $filters['updated_from']);
        if (!empty($filters['updated_to']))   $query->whereDate('record_updated_at', '<=', $filters['updated_to']);

        if (!empty($filters['created_from'])) $query->where(function ($sub) use ($filters) {
            $sub->whereDate('record_created_at', '>=', $filters['created_from'])
                ->orWhereDate('created_at', '>=', $filters['created_from']);
        });
        if (!empty($filters['created_to'])) $query->where(function ($sub) use ($filters) {
            $sub->whereDate('record_created_at', '<=', $filters['created_to'])
                ->orWhereDate('created_at', '<=', $filters['created_to']);
        });

        if (isset($filters['has_email']) && $filters['has_email'] !== '') {
            $filters['has_email'] === '1'
                ? $query->whereNotNull('email')->where('email', '!=', '')
                : $query->where(fn($s) => $s->whereNull('email')->orWhere('email', ''));
        }

        if (isset($filters['has_phone']) && $filters['has_phone'] !== '') {
            $filters['has_phone'] === '1'
                ? $query->whereNotNull('phone')->where('phone', '!=', '')
                : $query->where(fn($s) => $s->whereNull('phone')->orWhere('phone', ''));
        }

        if (isset($filters['has_linkedin']) && $filters['has_linkedin'] !== '') {
            $filters['has_linkedin'] === '1'
                ? $query->whereNotNull('linkedin_url')->where('linkedin_url', '!=', '')
                : $query->where(fn($s) => $s->whereNull('linkedin_url')->orWhere('linkedin_url', ''));
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
    // STREAMING FILE PARSERS
    // ─────────────────────────────────────────────────────────────────────

    /**
     * Stream a CSV file row-by-row.
     * Never loads the full file into memory — safe for 100k+ rows.
     */
    private function streamCsv(string $path, callable $callback): void
    {
        $handle = fopen($path, 'r');
        if ($handle === false) {
            throw new \RuntimeException('Cannot open CSV file.');
        }

        // Strip UTF-8 BOM if present
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $headers = null;
        while (($line = fgetcsv($handle, 0, ',')) !== false) {
            // Skip blank lines
            if ($line === [null]) continue;

            if ($headers === null) {
                $headers = $line;
                continue;
            }

            // Pad short rows to match header count
            while (count($line) < count($headers)) {
                $line[] = '';
            }

            $callback(array_combine($headers, array_slice($line, 0, count($headers))));
        }

        fclose($handle);
    }

    /**
     * Stream an XLSX/XLS file row-by-row.
     * Uses numeric column indices — safe for files with 27+ columns (AA, AB, …).
     * setReadDataOnly(true) cuts PhpSpreadsheet memory usage by ~60%.
     */
    private function streamXlsx(string $path, callable $callback): void
    {
        if (!class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
            throw new \RuntimeException(
                'XLSX support requires PhpSpreadsheet. Run: composer require phpoffice/phpspreadsheet'
            );
        }

        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true); // skip cell styles — major memory saving
        $spreadsheet = $reader->load($path);
        $sheet        = $spreadsheet->getActiveSheet();

        $headers    = null;
        $headerCount = 0;

        foreach ($sheet->getRowIterator() as $row) {
            $line          = [];
            $cellIterator  = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $val = $cell->getValue();

                // Excel stores dates as serial floats — convert them
                if (is_float($val) || is_int($val)) {
                    if (\PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
                        $val = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val)
                            ->format('Y-m-d H:i:s');
                    }
                }

                $line[] = ($val === null) ? '' : (string) $val;
            }

            if ($headers === null) {
                $headers     = array_map('trim', $line);
                $headerCount = count($headers);
                continue;
            }

            // Skip completely empty rows
            if (count(array_filter($line, fn($v) => $v !== '')) === 0) continue;

            // Pad / trim to header width
            while (count($line) < $headerCount) $line[] = '';

            $callback(array_combine($headers, array_slice($line, 0, $headerCount)));
        }

        // Free PhpSpreadsheet memory explicitly
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
    }
}