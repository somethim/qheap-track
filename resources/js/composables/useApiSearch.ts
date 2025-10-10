import { ref, type Ref, shallowRef } from 'vue';

export interface ApiSearchControls<TItem> {
    itemsList: Ref<TItem[]>;
    isLoading: Ref<boolean>;
    error: Ref<string | null>;
    performSearch: (search: string) => Promise<void>;
    clearResults: () => void;
}

export function useApiSearch<TItem extends Record<string, unknown>>(
    searchUrl: string,
    searchDelay: number = 300,
    responseKey: string = 'searchResults',
    searchParam: string = 'search',
): ApiSearchControls<TItem> {
    const itemsList = shallowRef<TItem[]>([]);
    const isLoading = ref<boolean>(false);
    const error = ref<string | null>(null);
    let searchTimeout: number | null = null;

    const clearSearchTimeout = () => {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
            searchTimeout = null;
        }
    };

    const performSearch = async (search: string): Promise<void> => {
        clearSearchTimeout();

        searchTimeout = window.setTimeout(async () => {
            const searchTerm = search.trim() === '' ? '' : search.trim();

            try {
                isLoading.value = true;
                error.value = null;

                const response = await fetch(searchUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN':
                            document
                                .querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') || '',
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                    body: JSON.stringify({ [searchParam]: searchTerm }),
                });

                if (response.ok) {
                    const data = await response.json();

                    if (data[responseKey]) {
                        itemsList.value = data[responseKey] as TItem[];
                    } else {
                        itemsList.value = [];
                    }
                } else {
                    itemsList.value = [];
                    error.value = `Search failed: ${response.status} ${response.statusText}`;
                }
            } catch {
                itemsList.value = [];
                error.value = 'Search request failed';
            } finally {
                isLoading.value = false;
            }
        }, searchDelay);
    };

    const clearResults = () => {
        itemsList.value = [];
        error.value = null;
        clearSearchTimeout();
    };

    return {
        itemsList,
        isLoading,
        error,
        performSearch,
        clearResults,
    };
}
