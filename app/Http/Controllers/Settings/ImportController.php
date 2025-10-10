<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Orders\Client;
use App\Models\Orders\Product;
use App\Models\Orders\Supplier;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class ImportController extends Controller
{
    /**
     * Show the import page.
     */
    public function edit(): Response
    {
        return Inertia::render('settings/Import');
    }

    /**
     * Handle CSV import.
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'resource' => ['required', Rule::in(['products', 'clients', 'suppliers'])],
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('error', 'Please fix the validation errors.');
        }

        $resource = $request->input('resource');
        $file = $request->file('file');

        try {
            $result = $this->processCSV($file, $resource);

            $message = "Successfully imported {$result['imported']} $resource.";

            if ($result['failed'] > 0) {
                $message .= " {$result['failed']} rows failed.";

                return back()->with('warning', $message);
            }

            return back()->with('success', $message);
        } catch (Throwable $e) {
            return back()->with('error', 'Import failed: '.$e->getMessage());
        }
    }

    /**
     * Process the CSV file and import records.
     *
     * @throws Throwable
     */
    private function processCSV($file, string $resource): array
    {
        $path = $file->getRealPath();

        $content = file_get_contents($path);
        $content = str_replace(["\r\n", "\r"], "\n", $content);
        $lines = explode("\n", $content);

        $csv = array_map(fn ($line) => str_getcsv($line), $lines);

        $csv = array_filter($csv, fn ($row) => ! empty(array_filter($row, fn ($cell) => trim($cell) !== '')));
        $csv = array_values($csv);

        if (empty($csv)) {
            throw new Exception('CSV file is empty');
        }

        $headers = array_map(fn ($h) => trim(strtolower($h)), $csv[0]);
        $data = array_slice($csv, 1);

        $imported = 0;
        $failed = 0;
        $errors = [];

        DB::transaction(function () use (&$headers, &$data, &$imported, &$failed, &$errors, $resource) {
            foreach ($data as $index => $row) {
                if (empty(array_filter($row, fn ($cell) => trim($cell) !== ''))) {
                    continue;
                }

                $row = array_map('trim', $row);

                $rowCount = count($row);
                $headerCount = count($headers);

                if ($rowCount < $headerCount) {
                    $row = array_pad($row, $headerCount, '');
                } elseif ($rowCount > $headerCount) {
                    $row = array_slice($row, 0, $headerCount);
                }

                $rowData = array_combine($headers, $row);

                $rowData = array_map(fn ($val) => $val === '' ? null : $val, $rowData);

                try {
                    switch ($resource) {
                        case 'products':
                            $this->importProduct($rowData);
                            break;
                        case 'clients':
                            $this->importClient($rowData);
                            break;
                        case 'suppliers':
                            $this->importSupplier($rowData);
                            break;
                    }
                    $imported++;
                } catch (Exception $e) {
                    $failed++;
                    $errors[] = 'Row '.($index + 2).': '.$e->getMessage();

                    if ($failed >= 10) {
                        throw new Exception('Too many errors. Import stopped. First 10 errors: '.implode(' | ', array_slice($errors, 0, 10)));
                    }
                }
            }
        });

        return [
            'imported' => $imported,
            'failed' => $failed,
            'errors' => $errors,
        ];
    }

    /**
     * Import a product from CSV row.
     *
     * @throws Exception
     */
    private function importProduct(array $data): void
    {
        $importData = array_intersect_key($data, array_flip(['name', 'sku', 'price', 'stock']));

        $validator = Validator::make($importData, [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: '.implode(', ', $validator->errors()->all()));
        }

        Product::create([
            'name' => $importData['name'],
            'sku' => $importData['sku'],
            'price' => $importData['price'],
            'stock' => $importData['stock'] ?? 0,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Import a client from CSV row.
     *
     * @throws Exception
     */
    private function importClient(array $data): void
    {
        $importData = array_intersect_key($data, array_flip(['name', 'description', 'contact_email', 'contact_phone', 'address']));

        $validator = Validator::make($importData, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: '.implode(', ', $validator->errors()->all()));
        }

        Client::create([
            'name' => $importData['name'],
            'description' => $importData['description'] ?? null,
            'contact_email' => $importData['contact_email'] ?? null,
            'contact_phone' => $importData['contact_phone'] ?? null,
            'address' => $importData['address'] ?? null,
            'user_id' => auth()->id(),
        ]);
    }

    /**
     * Import a supplier from CSV row.
     *
     * @throws Exception
     */
    private function importSupplier(array $data): void
    {
        $importData = array_intersect_key($data, array_flip(['name', 'description', 'contact_email', 'contact_phone', 'address']));

        $validator = Validator::make($importData, [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'contact_email' => ['nullable', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new Exception('Validation failed: '.implode(', ', $validator->errors()->all()));
        }

        Supplier::create([
            'name' => $importData['name'],
            'description' => $importData['description'] ?? null,
            'contact_email' => $importData['contact_email'] ?? null,
            'contact_phone' => $importData['contact_phone'] ?? null,
            'address' => $importData['address'] ?? null,
            'user_id' => auth()->id(),
        ]);
    }
}
