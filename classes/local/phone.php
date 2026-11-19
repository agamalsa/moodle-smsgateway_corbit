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

    /** @var int Longest number accepted as stored, hidden marks included. */
    public const MAX_RAW_LENGTH = 200;

    /** @var int Longest number accepted as shown, after hidden marks are removed. */
    public const MAX_VISIBLE_LENGTH = 40;

    /**
     * Converts a number as typed (spaces, dashes, parentheses, +, 00, a leading 0, Arabic digits) to international digits.
     *
     * Nothing is guessed: a number with no +, 00 or leading 0 is rejected, except, on a Saudi gateway, a Saudi
     * mobile typed without its 0 (5XXXXXXXX) or without its + (9665XXXXXXXX). A number that starts with
     * + or 00 and another country code is never turned into a Saudi number. Decimals (966551234567.0) and
     * exponents (9.66551E+11) are rejected. Parentheses must be balanced, not nested, and not at the end:
     * in +971501234567 (1) the (1) is a note or an extension, and keeping its digit would give another
     * person's number.
     *
     * @param string $raw the number as stored in Moodle
     * @param string|null $countrycode country code for local numbers, such as 966; empty or 0 means Saudi Arabia
     * @return string digits such as 966551234567, or '' when it is not a number that can receive SMS
     */
    public static function normalise(string $raw, ?string $countrycode = null): string {
        // A country code may be entered as 966, +966 or 00966.
        $cc = ltrim(preg_replace('/\D+/', '', (string) $countrycode), '0');
        if ($cc === '') {
            $cc = self::DEFAULT_COUNTRY_CODE;
        }
        if (\core_text::strlen($raw) > self::MAX_RAW_LENGTH) {
            return '';
        }
        // Hidden direction marks and no-break spaces copied with the number.
        $raw = trim((string) preg_replace('/[\p{Cf}\x{00A0}\x{202F}]+/u', '', $raw));
        if (\core_text::strlen($raw) > self::MAX_VISIBLE_LENGTH) {
            return '';
        }
        // Only digits, spaces, dashes (also the ones Word and Excel type) and parentheses, with one + at the start,
        // which may follow an opening parenthesis: (+966) 55 123 4567.
        if ($raw === '' || !preg_match('/^\(?\s*\+?[0-9\x{0660}-\x{0669}\x{06F0}-\x{06F9}()\p{Pd}\x{2212}\s]+$/u', $raw)) {
            return '';
        }
        if (!self::brackets_ok($raw)) {
            return '';
        }
        $digits = preg_replace('/\D+/', '', self::western_digits($raw));
        if ($digits === '') {
            return '';
        }

        // The same opening as the check above, so any white space before the + (U+3000, form feed, new line...) counts:
        // a + number is never read as a local one.
        if (preg_match('/^\(?\s*\+/u', $raw)) {
            $international = $digits;
        } else if (str_starts_with($digits, '00')) {
            $international = substr($digits, 2);
        } else if (str_starts_with($digits, '0')) {
            // A local number: the gateway's country code replaces the leading 0.
            $international = $cc . substr($digits, 1);
        } else if ($cc === '966' && strlen($digits) === 9 && $digits[0] === '5') {
            // A Saudi mobile typed without its leading 0: 5XXXXXXXX.
            $international = '966' . $digits;
        } else if ($cc === '966' && strlen($digits) === 12 && str_starts_with($digits, '9665')) {
            // Saudi international digits without + (966551234567): no Saudi local number looks like this.
            // Other countries are not guessed, because a local number there can start with the country code.
            $international = $digits;
        } else {
            // No country code and not a known local form: do not guess where it should go.
            return '';
        }

        if ($international === '' || $international[0] === '0') {
            return '';
        }
        if (str_starts_with($international, '966')) {
            // Saudi Arabia: mobiles only (9665XXXXXXXX), also written +966 05XXXXXXXX. Landlines cannot receive SMS.
            $local = substr($international, 3);
            if (str_starts_with($local, '0')) {
                $local = substr($local, 1);
            }
            return preg_match('/^5\d{8}$/', $local) ? '966' . $local : '';
        }
        $length = strlen($international);
        return ($length >= 8 && $length <= 15) ? $international : '';
    }

    /**
     * Parentheses are balanced, not nested, and the number does not end with one.
     *
     * A closing parenthesis at the end, even followed by spaces or dashes, marks a note such as (1) or (101).
     * Opening and closing parentheses are ASCII, so a byte loop is safe in UTF-8.
     *
     * @param string $text the number without hidden marks
     * @return bool
     */
    private static function brackets_ok(string $text): bool {
        $open = false;
        $length = strlen($text);
        for ($i = 0; $i < $length; $i++) {
            if ($text[$i] === '(') {
                if ($open) {
                    return false;
                }
                $open = true;
            } else if ($text[$i] === ')') {
                if (!$open) {
                    return false;
                }
                $open = false;
            }
        }
        return !$open && !preg_match('/\)[\s\p{Pd}\x{2212}]*$/u', $text);
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
