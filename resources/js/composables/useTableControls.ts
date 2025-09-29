import type { Pagination } from '@/types';
import type { QueryParams } from '@/wayfinder';

const getCurrentParams = () => {
    const params = new URLSearchParams(window.location.search);
    return {
        type: params.get('type') || 'client',
        startDate: params.get('start_date'),
        endDate: params.get('end_date'),
        perPage: params.get('per_page'),
    };
};

export function useTableControls(
    pagination: Pagination,
    onNavigate: (query: QueryParams) => void,
) {
    const handlePageChange = (page: number) => {
        const currentParams = getCurrentParams();
        const maxPage = pagination.lastPage;

        const targetPage = Math.max(1, Math.min(page, maxPage));

        onNavigate({
            page: targetPage,
            per_page: currentParams.perPage || pagination.perPage,
            type: currentParams.type,
            start_date: currentParams.startDate,
            end_date: currentParams.endDate,
        });
    };

    const handlePageSizeChange = (pageSize: number | string) => {
        const currentParams = getCurrentParams();
        const newPageSize = Number(pageSize);
        const totalItems = pagination.total;
        const maxPage = Math.ceil(totalItems / newPageSize);

        let targetPage = pagination.currentPage;
        if (targetPage > maxPage) {
            targetPage = Math.max(1, maxPage);
        }

        onNavigate({
            page: targetPage,
            per_page: pageSize,
            type: currentParams.type,
            start_date: currentParams.startDate,
            end_date: currentParams.endDate,
        });
    };

    const handleFiltersChange = (filters: {
        startDate: string | null;
        endDate: string | null;
    }) => {
        const currentParams = getCurrentParams();
        onNavigate({
            page: 1,
            per_page: currentParams.perPage || pagination.perPage,
            type: currentParams.type,
            start_date: filters.startDate,
            end_date: filters.endDate,
        });
    };

    return {
        handlePageChange,
        handlePageSizeChange,
        handleFiltersChange,
    };
}
