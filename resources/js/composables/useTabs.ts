import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive, watch } from 'vue';
import { dashboard } from '@/routes';

export interface Tab {
    id: string;
    title: string;
    url: string;
    component: string;
    props: Record<string, any>;
    closable: boolean;
    cachedAt: number;
}

interface TabState {
    tabs: Tab[];
    activeTabId: string | null;
}

const loadStateFromStorage = (): TabState => {
    try {
        const stored = localStorage.getItem('tabs-state');
        if (stored) {
            const parsed = JSON.parse(stored);
            if (parsed.tabs && Array.isArray(parsed.tabs)) {
                return {
                    tabs: parsed.tabs,
                    activeTabId: parsed.activeTabId || null,
                };
            }
        }
    } catch (e) {
        console.error('[useTabs] Failed to load state from localStorage:', e);
    }
    return {
        tabs: [],
        activeTabId: null,
    };
};

const saveStateToStorage = (state: TabState) => {
    try {
        localStorage.setItem('tabs-state', JSON.stringify({
            tabs: state.tabs,
            activeTabId: state.activeTabId,
        }));
    } catch (e) {
        console.error('[useTabs] Failed to save state to localStorage:', e);
    }
};

const state = reactive<TabState>(loadStateFromStorage());

const page = usePage();

let watcherInitialized = false;

const normalizeUrl = (url: string): string => {
    const urlWithoutQuery = url.split('?')[0].split('#')[0];
    
    if (urlWithoutQuery.match(/^\/settings(\/|$)/)) {
        return '/settings';
    }
    
    return urlWithoutQuery;
};

const generateTabId = (url: string): string => {
    const normalized = normalizeUrl(url);
    return `tab-${normalized.replace(/[^a-zA-Z0-9]/g, '-')}`;
};

const getPageTitle = (): string => {
    const component = page.component;
    const breadcrumbs = page.props.breadcrumbs as any[];

    if (component.startsWith('settings/') || component === 'settings') {
        return 'Settings';
    }

    if (breadcrumbs && breadcrumbs.length > 0) {
        const lastCrumb = breadcrumbs[breadcrumbs.length - 1];
        return lastCrumb.label || lastCrumb.title || 'Page';
    }

    const parts = component.split('/');

    if (parts.length >= 2) {
        const section = parts[0];
        const action = parts[parts.length - 1];

        const sectionTitle = section.charAt(0).toUpperCase() + section.slice(1);
        const singularTitle = sectionTitle.slice(0, -1);

        if (action === 'create') return `New ${singularTitle}`;
        if (action === 'index') return sectionTitle;

        if (action === 'show') {
            const props = page.props as any;

            let name = null;

            if (section === 'clients' && props.client?.name) {
                name = props.client.name;
            } else if (section === 'suppliers' && props.supplier?.name) {
                name = props.supplier.name;
            } else if (section === 'products' && props.product?.name) {
                name = props.product.name;
            } else if (section === 'orders' && props.order?.order_number) {
                name = props.order.order_number;
            } else if (props.name) {
                name = props.name;
            } else if (props.data?.name) {
                name = props.data.name;
            }

            if (name) {
                const firstName = String(name).split(/[\s-]+/)[0];
                const shortName =
                    firstName.length > 20
                        ? firstName.substring(0, 20) + '...'
                        : firstName;
                return `${singularTitle}: ${shortName}`;
            }

            const urlParts = page.url.split('/').filter((p) => p);
            const idFromUrl = urlParts[urlParts.length - 1];

            if (!isNaN(Number(idFromUrl))) {
                return `${singularTitle} #${idFromUrl}`;
            }

            return `View ${singularTitle}`;
        }

        return sectionTitle;
    }

    if (component === 'Dashboard') return 'Dashboard';

    return component;
};

const addTab = (url?: string, title?: string, closable: boolean = true) => {
    const targetUrl = url || page.url;
    const tabId = generateTabId(targetUrl);

    const existingTab = state.tabs.find((tab) => tab.id === tabId);

    if (existingTab) {
        state.activeTabId = tabId;
        if (!url) {
            existingTab.props = page.props as Record<string, any>;
        }
    } else {
        const newTab: Tab = {
            id: tabId,
            title: title || getPageTitle(),
            url: targetUrl,
            component: page.component,
            props: page.props as Record<string, any>,
            closable,
            cachedAt: Date.now(),
        };

        state.tabs.push(newTab);
        state.activeTabId = tabId;
    }
    saveStateToStorage(state);
};

const updateActiveTab = () => {
    if (!state.activeTabId) return;

    const activeTab = state.tabs.find((tab) => tab.id === state.activeTabId);
    if (activeTab) {
        activeTab.url = page.url;
        activeTab.component = page.component;
        activeTab.props = page.props as Record<string, any>;
        activeTab.title = getPageTitle();
        saveStateToStorage(state);
    }
};

if (!watcherInitialized) {
    watcherInitialized = true;

    watch(
        () => page.url,
        (newUrl) => {
            const tabId = generateTabId(newUrl);
            const existingTab = state.tabs.find((tab) => tab.id === tabId);

            if (existingTab) {
                state.activeTabId = tabId;
                updateActiveTab();
            } else {
                addTab(newUrl, getPageTitle(), true);
            }
        },
    );
}

export function useTabs() {
    const removeTab = (tabId: string) => {
        const index = state.tabs.findIndex((tab) => tab.id === tabId);
        if (index === -1) return;

        const tab = state.tabs[index];
        if (!tab.closable) return;

        state.tabs.splice(index, 1);

        if (state.activeTabId === tabId) {
            if (state.tabs.length > 0) {
                const newIndex = Math.max(0, index - 1);
                state.activeTabId = state.tabs[newIndex].id;
                router.visit(state.tabs[newIndex].url, { preserveState: true });
            } else {
                state.activeTabId = null;
                router.visit(dashboard().url, { preserveState: false });
            }
        }
        
        saveStateToStorage(state);
    };

    const switchTab = (tabId: string) => {
        const tab = state.tabs.find((t) => t.id === tabId);
        if (tab) {
            state.activeTabId = tabId;
            if (page.url !== tab.url) {
                router.visit(tab.url, {
                    preserveScroll: true,
                    preserveState: false,
                });
            }
        }
    };

    const closeOtherTabs = (tabId: string) => {
        state.tabs = state.tabs.filter(
            (tab) => tab.id === tabId || !tab.closable,
        );
        if (state.activeTabId !== tabId) {
            const tab = state.tabs.find((t) => t.id === tabId);
            if (tab) {
                state.activeTabId = tabId;
                router.visit(tab.url);
            }
        }
        saveStateToStorage(state);
    };

    const closeAllTabs = () => {
        state.tabs = [];
        state.activeTabId = null;
        
        router.visit(dashboard().url, { preserveState: false });
        
        saveStateToStorage(state);
    };

    const closeTabsToRight = (tabId: string) => {
        const index = state.tabs.findIndex((tab) => tab.id === tabId);
        if (index === -1) return;

        const tabsToKeep = state.tabs.slice(0, index + 1);
        const removedTabs = state.tabs
            .slice(index + 1)
            .filter((tab) => tab.closable);

        state.tabs = [
            ...tabsToKeep,
            ...state.tabs.slice(index + 1).filter((tab) => !tab.closable),
        ];

        const wasActiveRemoved = removedTabs.some(
            (tab) => tab.id === state.activeTabId,
        );
        if (wasActiveRemoved) {
            state.activeTabId = tabId;
            const tab = state.tabs.find((t) => t.id === tabId);
            if (tab) {
                router.visit(tab.url);
            }
        }
        saveStateToStorage(state);
    };

    const initialize = () => {
        if (state.tabs.length === 0) {
            addTab(page.url, getPageTitle(), true);
        }
        saveStateToStorage(state);
    };

    return {
        tabs: computed(() => state.tabs),
        activeTabId: computed(() => state.activeTabId),
        addTab,
        removeTab,
        switchTab,
        closeOtherTabs,
        closeAllTabs,
        closeTabsToRight,
        updateActiveTab,
        initialize,
    };
}
