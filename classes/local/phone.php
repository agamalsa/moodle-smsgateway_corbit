<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace smsgateway_corbit\local;

/**
 * Mobile numbers: international digits for the Corbit API, and numbers that cannot receive SMS rejected.
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class phone {
    /** @var string Country code used when the gateway has none: Saudi Arabia. */
    public const DEFAULT_COUNTRY_CODE = '966';

    /**
     * Converts a number as typed (spaces, dashes, +, 00, a leading 0, Arabic digits) to international digits.
     *
     * @param string $raw the number as stored in Moodle
     * @param string|null $countrycode country code for local numbers, such as 966; empty or 0 means Saudi Arabia
     * @return string digits such as 966551234567, or '' when it is not a number that can receive SMS
     */
    public static function normalise(string $raw, ?string $countrycode = null): string {
        $cc = preg_replace('/\D+/', '', (string) $countrycode);
        if ($cc === '' || (int) $cc === 0) {
            $cc = self::DEFAULT_COUNTRY_CODE;
        }
        $digits = preg_replace('/\D+/', '', self::western_digits($raw));
        if ($digits === '') {
            return '';
        }
        $international = str_starts_with(trim($raw), '+');
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        } else if (!$international && str_starts_with($digits, '0')) {
            $digits = $cc . substr($digits, 1);
        } else if (!$international && $cc === '966' && strlen($digits) === 9 && $digits[0] === '5') {
            // A Saudi mobile typed without its leading 0: 5XXXXXXXX.
            $digits = '966' . $digits;
        }

        if (str_starts_with($digits, '966')) {
            // Saudi Arabia: mobiles only (9665XXXXXXXX). Landlines cannot receive SMS.
            return preg_match('/^9665\d{8}$/', $digits) ? $digits : '';
        }
        $length = strlen($digits);
        return ($length >= 8 && $length <= 15) ? $digits : '';
    }

    /**
     * Shows the last four digits only.
     *
     * @param string $number
     * @return string
     */
    public static function mask(string $number): string {
        $length = \core_text::strlen($number);
        return $length > 4 ? str_repeat('*', $length - 4) . \core_text::substr($number, -4) : '****';
    }

    /**
     * Arabic-Indic and Persian digits become 0-9.
     *
     * @param string $text
     * @return string
     */
    private static function western_digits(string $text): string {
        $map = [];
        foreach (['٠١٢٣٤٥٦٧٨٩', '۰۱۲۳۴۵۶۷۸۹'] as $digits) {
            foreach (mb_str_split($digits) as $value => $digit) {
                $map[$digit] = (string) $value;
            }
        }
        return strtr($text, $map);
    }
}
