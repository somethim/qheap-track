<script lang="ts" setup>
import type { HTMLAttributes } from 'vue';
import { ref, watch } from 'vue';
import { cn } from '@/lib/utils';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export interface CountryCode {
    code: string;
    name: string;
    flag: string;
}

const props = defineProps<{
    modelValue?: string;
    defaultValue?: string;
    defaultCountryCode?: string;
    class?: HTMLAttributes['class'];
    placeholder?: string;
    disabled?: boolean;
}>();

const emits = defineEmits<{
    (e: 'update:modelValue', payload: string): void;
}>();

const countryCodes: CountryCode[] = [
    { code: '+355', name: 'Albania', flag: '🇦🇱' },
    { code: '+1', name: 'USA/Canada', flag: '🇺🇸' },
    { code: '+44', name: 'UK', flag: '🇬🇧' },
    { code: '+49', name: 'Germany', flag: '🇩🇪' },
    { code: '+33', name: 'France', flag: '🇫🇷' },
    { code: '+39', name: 'Italy', flag: '🇮🇹' },
    { code: '+34', name: 'Spain', flag: '🇪🇸' },
    { code: '+30', name: 'Greece', flag: '🇬🇷' },
    { code: '+90', name: 'Turkey', flag: '🇹🇷' },
    { code: '+381', name: 'Serbia', flag: '🇷🇸' },
    { code: '+383', name: 'Kosovo', flag: '🇽🇰' },
    { code: '+389', name: 'North Macedonia', flag: '🇲🇰' },
];

const formatPhoneNumber = (value: string): string => {
    const digits = value.replace(/\D/g, '');

    if (digits.length === 0) return '';
    if (digits.length <= 3) return digits;
    if (digits.length <= 6) return `${digits.slice(0, 3)} ${digits.slice(3)}`;
    if (digits.length <= 9) return `${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6)}`;
    return `${digits.slice(0, 3)} ${digits.slice(3, 6)} ${digits.slice(6, 10)}`;
};

const parsePhoneNumber = (fullNumber: string): { countryCode: string; localNumber: string } => {
    if (!fullNumber) {
        return { countryCode: props.defaultCountryCode || '+355', localNumber: '' };
    }

    const trimmedNumber = fullNumber.trim();
    const matchedCountry = countryCodes.find((country) => trimmedNumber.startsWith(country.code));

    if (matchedCountry) {
        const localPart = trimmedNumber.substring(matchedCountry.code.length).trim();
        const digits = localPart.replace(/\D/g, '');
        return {
            countryCode: matchedCountry.code,
            localNumber: formatPhoneNumber(digits),
        };
    }

    const digits = trimmedNumber.replace(/\D/g, '');
    return {
        countryCode: props.defaultCountryCode || '+355',
        localNumber: formatPhoneNumber(digits)
    };
};

const { countryCode: initialCountryCode, localNumber: initialLocalNumber } = parsePhoneNumber(
    props.modelValue || props.defaultValue || ''
);

const selectedCountryCode = ref(initialCountryCode);
const localPhoneNumber = ref(initialLocalNumber);

watch(
    () => props.modelValue,
    (newValue) => {
        if (newValue !== undefined) {
            const { countryCode, localNumber } = parsePhoneNumber(newValue);
            selectedCountryCode.value = countryCode;
            localPhoneNumber.value = localNumber;
        }
    }
);

const handleLocalNumberInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const rawValue = target.value;

    const digits = rawValue.replace(/\D/g, '');

    const formatted = formatPhoneNumber(digits);
    localPhoneNumber.value = formatted;

    const fullPhoneNumber = formatted ? `${selectedCountryCode.value} ${formatted}` : '';
    emits('update:modelValue', fullPhoneNumber);
};

const handleCountryCodeChange = (newCode: unknown) => {
    if (!newCode || typeof newCode !== 'string') return;

    selectedCountryCode.value = newCode;

    const fullPhoneNumber = localPhoneNumber.value
        ? `${newCode} ${localPhoneNumber.value}`
        : '';
    emits('update:modelValue', fullPhoneNumber);
};
</script>

<template>
    <div :class="cn('flex gap-2', props.class)">
        <Select
            :disabled="disabled"
            :model-value="selectedCountryCode"
            @update:model-value="handleCountryCodeChange"
        >
            <SelectTrigger class="w-[120px] shrink-0">
                <SelectValue>
                    <span class="flex items-center gap-2">
                        <span>{{ countryCodes.find(c => c.code === selectedCountryCode)?.flag }}</span>
                        <span>{{ selectedCountryCode }}</span>
                    </span>
                </SelectValue>
            </SelectTrigger>
            <SelectContent>
                <SelectGroup>
                    <SelectItem
                        v-for="country in countryCodes"
                        :key="country.code"
                        :value="country.code"
                    >
                        <span class="flex items-center gap-2">
                            <span>{{ country.flag }}</span>
                            <span>{{ country.code }}</span>
                            <span class="text-muted-foreground text-xs">{{ country.name }}</span>
                        </span>
                    </SelectItem>
                </SelectGroup>
            </SelectContent>
        </Select>

        <input
            :class="
                cn(
                    'file:text-foreground placeholder:text-muted-foreground selection:bg-primary selection:text-primary-foreground dark:bg-input/30 border-input flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive'
                )
            "
            :disabled="disabled"
            :placeholder="placeholder || 'Enter phone number'"
            :value="localPhoneNumber"
            type="tel"
            @input="handleLocalNumberInput"
        />
    </div>
</template>
