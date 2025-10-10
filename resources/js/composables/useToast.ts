import { toast as sonnerToast } from 'vue-sonner';

export function useToast() {
    return {
        toast: sonnerToast,
        success: (message: string, description?: string) => {
            sonnerToast.success(message, { description });
        },
        error: (message: string, description?: string) => {
            sonnerToast.error(message, { description });
        },
        info: (message: string, description?: string) => {
            sonnerToast.info(message, { description });
        },
        warning: (message: string, description?: string) => {
            sonnerToast.warning(message, { description });
        },
    };
}

