<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Orders\Client;
use App\Models\Orders\Product;
use App\Models\Orders\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ExportController extends Controller
{
    /**
     * Show the export page.
     */
    public function edit(): InertiaResponse
    {
        $stats = [
            'products' => Product::count(),
            'clients' => Client::count(),
            'suppliers' => Supplier::count(),
        ];

        return Inertia::render('settings/Export', [
            'stats' => $stats,
        ]);
    }

    /**
     * Handle CSV export.
     */
    public function store(Request $request)
    {
        $request->validate([
            'resource' => ['required', Rule::in(['products', 'clients', 'suppliers'])],
            'include_headers' => ['nullable', 'boolean'],
        ]);

        $resource = $request->input('resource');
        $includeHeaders = $request->boolean('include_headers', true);

        try {
            $data = $this->getData($resource);
            $csv = $this->generateCSV($data, $includeHeaders);

            $filename = $resource.'_export_'.now()->format('Y-m-d_His').'.csv';

            return Response::make($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
                'Pragma' => 'public',
            ]);
        } catch (Exception $e) {
            return back()->with('exportStatus', [
                'type' => 'error',
                'message' => 'Export failed: '.$e->getMessage(),
            ]);
        }
    }

    /**
     * Get data for export based on resource type.
     *
     * @throws Exception
     */
    private function getData(string $resource): array
    {
        return match ($resource) {
            'products' => $this->getProducts(),
            'clients' => $this->getClients(),
            'suppliers' => $this->getSuppliers(),
            default => throw new Exception('Invalid resource type'),
        };
    }

    /**
     * Get products data.
     */
    private function getProducts(): array
    {
        $products = Product::orderBy('created_at', 'desc')->get();

        $headers = ['id', 'name', 'sku', 'price', 'stock', 'created_at', 'updated_at'];
        $rows = [];

        foreach ($products as $product) {
            $rows[] = [
                $product->id,
                $product->name,
                $product->sku,
                $product->price,
                $product->stock,
                $product->created_at->format('Y-m-d H:i:s'),
                $product->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Get clients data.
     */
    private function getClients(): array
    {
        $clients = Client::orderBy('created_at', 'desc')->get();

        return $this->get_supplier_and_client($clients);
    }

    private function get_supplier_and_client(Collection $origins): array
    {
        $headers = ['id', 'name', 'description', 'contact_email', 'contact_phone', 'address', 'created_at', 'updated_at'];
        $rows = [];

        foreach ($origins as $origin) {
            $rows[] = [
                $origin->id,
                $origin->name,
                $origin->description ?? '',
                $origin->contact_email ?? '',
                $origin->contact_phone ?? '',
                $origin->address ?? '',
                $origin->created_at->format('Y-m-d H:i:s'),
                $origin->updated_at->format('Y-m-d H:i:s'),
            ];
        }

        return ['headers' => $headers, 'rows' => $rows];
    }

    /**
     * Get suppliers data.
     */
    private function getSuppliers(): array
    {
        $suppliers = Supplier::orderBy('created_at', 'desc')->get();

        return $this->get_supplier_and_client($suppliers);
    }

    /**
     * Generate CSV string from data.
     */
    private function generateCSV(array $data, bool $includeHeaders): string
    {
        $csv = [];

        if ($includeHeaders) {
            $csv[] = $this->escapeCSVRow($data['headers']);
        }

        foreach ($data['rows'] as $row) {
            $csv[] = $this->escapeCSVRow($row);
        }

        return implode("\n", $csv);
    }

    /**
     * Escape and format a CSV row.
     */
    private function escapeCSVRow(array $row): string
    {
        $escaped = array_map(function ($field) {
            $field = (string) $field;

            if (str_contains($field, ',') || str_contains($field, '"') || str_contains($field, "\n")) {
                $field = '"'.str_replace('"', '""', $field).'"';
            }

            return $field;
        }, $row);

        return implode(',', $escaped);
    }
}
