<?php

namespace App\Utils;

use App\Enums\CharacterSet;
use Exception;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;
use Random\RandomException;

class ContactVerificationUtils
{
    private static array $dnsCache = [];

    /**
     * @throws RandomException
     */
    public static function generateRandomCode(int $length = 6, CharacterSet $type = CharacterSet::Numeric): string
    {
        $characters = $type->getCharacters();
        $code = '';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $code;
    }

    /**
     * Check if email address is valid
     */
    public static function validateEmailAddress(?string $email): bool
    {
        try {
            if (empty($email)) {
                return false;
            }

            $cleanEmailAddress = static::cleanEmailAddress($email);

            if (! filter_var($cleanEmailAddress, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $domain = strtolower(substr(strrchr($cleanEmailAddress, '@'), 1));

            return self::validateDomain($domain);
        } catch (Exception) {
            return false;
        }
    }

    public static function cleanEmailAddress(?string $email): ?string
    {
        if (empty($email)) {
            return null;
        }

        return preg_replace('/^([^+@]+)\+[^@]*@(.+)$/', '$1@$2', $email);
    }

    private static function validateDomain(string $domain): bool
    {
        if (isset(self::$dnsCache[$domain])) {
            return self::$dnsCache[$domain];
        }

        try {
            $originalTimeout = ini_get('default_socket_timeout');
            ini_set('default_socket_timeout', 5);

            $isValid = @checkdnsrr($domain);
            if (! $isValid) {
                $isValid = @checkdnsrr($domain, 'A');
            }

            ini_set('default_socket_timeout', $originalTimeout);
        } catch (Exception) {
            $isValid = false;
        }
        self::$dnsCache[$domain] = $isValid;

        return $isValid;
    }

    /**
     * Check if phone number is valid
     */
    public static function validatePhoneNumber(?string $phone, ?string $defaultRegion = null): bool
    {
        if (empty($phone)) {
            return false;
        }

        try {
            $phoneUtil = PhoneNumberUtil::getInstance();
            $phoneNumber = $phoneUtil->parse($phone, $defaultRegion);

            return $phoneUtil->isValidNumber($phoneNumber);
        } catch (NumberParseException) {
            return false;
        }
    }

    /**
     * Clear DNS cache - useful for testing or long-running processes
     */
    public static function clearDnsCache(): void
    {
        self::$dnsCache = [];
    }
}
