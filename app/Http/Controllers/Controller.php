<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource, paginated.
     */
    protected function paginate(Request $request, Builder $query): array
    {
        $perPageParam = $request->query('per_page', 50);
        $perPage = max(1, (int) $perPageParam);
        $page = max(1, (int) ($request->query('page', 1)));

        $paginatedQuery = $query->paginate(perPage: $perPage, page: $page);

        return [
            'items' => $paginatedQuery->items(),
            'pagination' => [
                'firstPage' => 1,
                'currentPage' => $paginatedQuery->currentPage(),
                'lastPage' => $paginatedQuery->lastPage(),
                'firstPageUrl' => $paginatedQuery->url(1),
                'lastPageUrl' => $paginatedQuery->url($paginatedQuery->lastPage()),
                'perPage' => $paginatedQuery->perPage(),
                'nextPageUrl' => $paginatedQuery->nextPageUrl(),
                'prevPageUrl' => $paginatedQuery->previousPageUrl(),
                'total' => $paginatedQuery->total(),
                'hasMorePages' => $paginatedQuery->hasMorePages(),
            ],
        ];
    }
}
