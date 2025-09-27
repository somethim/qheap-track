<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource, paginated.
     */
    protected function paginate(Request $request, array $data): array
    {
        $perPage = max(1, (int) ($request->query('perPage', 15)));
        $page = $request->integer($request->query('page', 1));
        $collection = collect($data);

        $paginator = new LengthAwarePaginator(
            $collection->forPage($page, $perPage),
            $collection->count(),
            $perPage,
            $page,
            ['path' => $request->url()]
        );

        return [
            'items' => $paginator->items(),
            'pagination' => [
                'firstPage' => 1,
                'currentPage' => $paginator->currentPage(),
                'lastPage' => $paginator->lastPage(),
                'firstPageUrl' => $paginator->url(1),
                'lastPageUrl' => $paginator->url($paginator->lastPage()),
                'perPage' => $paginator->perPage(),
                'nextPageUrl' => $paginator->nextPageUrl(),
                'prevPageUrl' => $paginator->previousPageUrl(),
                'total' => $paginator->total(),
                'hasMorePages' => $paginator->hasPages(),
            ],
        ];
    }
}
