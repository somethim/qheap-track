import { router, usePage } from '@inertiajs/vue3';
import { useToast } from './useToast';

export interface FlashMessages {
    success?: string;
    error?: string;
    info?: string;
    warning?: string;
}

export function useFlashMessages() {
    const { success, error, info, warning } = useToast();

    router.on('finish', () => {
        const page = usePage();
        const props = page.props as any;

        if (props.success) {
            success(props.success);
        }

        if (props.error) {
            error(props.error);
        }

        if (props.info) {
            info(props.info);
        }

        if (props.warning) {
            warning(props.warning);
        }

        if (props.status) {
            info(props.status);
        }
    });
}

