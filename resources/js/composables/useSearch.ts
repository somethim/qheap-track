import { ref, type Ref, watch } from 'vue';

export interface SearchControls {
    searchQuery: Ref<string>;
    setSearchQuery: (query: string) => void;
    clearSearch: () => void;
}

export function useSearch(
    initialQuery: string = '',
    onSearch?: (query: string) => void,
): SearchControls {
    const searchQuery = ref<string>(initialQuery);

    const setSearchQuery = (query: string) => {
        searchQuery.value = query;
    };

    const clearSearch = () => {
        searchQuery.value = '';
    };

    // Watch for changes and call the search callback
    if (onSearch) {
        watch(searchQuery, (newValue) => {
            onSearch(newValue);
        });
    }

    return {
        searchQuery,
        setSearchQuery,
        clearSearch,
    };
}
