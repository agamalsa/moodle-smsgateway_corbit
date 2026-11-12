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
 * Corbit SMS API (https://api.corbit.sa/api/v1) with the account's own API token.
 *
 * Three calls: send a message, list the account's sender names and read the SMS balance.
 * Requests go through Moodle's curl class, so the site's proxy and security settings apply.
 *
 * @package    smsgateway_corbit
 * @copyright  2026 Corbit Company
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class api {
    /** @var string Default API address. */
    public const DEFAULT_URL = 'https://api.corbit.sa/api/v1';

    /** @var string Result: accepted by Corbit. */
    public const RESULT_SENT = 'sent';
    /** @var string Result: Corbit refused the request (token, sender name, number, balance). Sending again will not help. */
    public const RESULT_REJECTED = 'rejected';
    /** @var string Result: no answer or a server error. Sending later may work. */
    public const RESULT_FAILED = 'failed';

    /**
     * Constructor.
     *
     * @param string $token the Corbit API token
     */
    public function __construct(
        /** @var string the Corbit API token */
        private readonly string $token,
    ) {
    }

    /**
     * The API address: the default, unless config.php sets another one with
     * $CFG->forced_plugin_settings['smsgateway_corbit']['apiurl'] (for a test server, for example).
     *
     * The API token is sent to this address, so it must be HTTPS. Plain HTTP is accepted only for a server
     * on this computer (localhost or 127.0.0.1) while developer debugging is on.
     *
     * @return string
     */
    public static function base_url(): string {
        global $CFG;
        $url = trim((string) get_config('smsgateway_corbit', 'apiurl'));
        if ($url === '') {
            return self::DEFAULT_URL;
        }
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $local = in_array($host, ['localhost', '127.0.0.1'], true) && !empty($CFG->debugdeveloper);
        if ($host === '' || parse_url($url, PHP_URL_USER) !== null || ($scheme !== 'https' && !($scheme === 'http' && $local))) {
            debugging('smsgateway_corbit: apiurl must be an https:// address; the default address is used.', DEBUG_DEVELOPER);
            return self::DEFAULT_URL;
        }
        return rtrim($url, '/');
    }

    /**
     * Sends one SMS.
     *
     * @param string $number international digits, such as 966551234567
     * @param string $sender an approved sender name of the account
     * @param string $text the message
     * @return array{result: string, id: string, error: string}
     */
    public function send(string $number, string $sender, string $text): array {
        [$code, $body, $error] = $this->request('POST', '/send', [
            'number' => $number,
            'senderName' => $sender,
            'sendAtOption' => 'Now',
            'messageBody' => $text,
            'allow_duplicate' => true,
        ]);
        if ($error !== '') {
            return ['result' => self::RESULT_FAILED, 'id' => '', 'error' => $error];
        }
        $status = is_array($body) && isset($body['status']) ? strtolower((string) $body['status']) : '';
        if ($code >= 200 && $code < 300 && $status === 'success') {
            $id = isset($body['data']['message']['id']) ? clean_param((string) $body['data']['message']['id'], PARAM_TEXT) : '';
            return ['result' => self::RESULT_SENT, 'id' => $id, 'error' => ''];
        }
        $result = ($code >= 500 || $code === 429 || $code === 0) ? self::RESULT_FAILED : self::RESULT_REJECTED;
        return ['result' => $result, 'id' => '', 'error' => self::error_text($code, $body)];
    }

    /**
     * Approved sender names of the account.
     *
     * @return string[]
     * @throws \moodle_exception when the account cannot be read
     */
    public function approved_sender_names(): array {
        [$code, $body, $error] = $this->request('GET', '/sendernames?per_page=100&page=1');
        if ($error !== '' || $code < 200 || $code >= 300 || !isset($body['data']) || !is_array($body['data'])) {
            throw self::exception($code, $body, $error);
        }
        $names = [];
        foreach ($body['data'] as $row) {
            if (is_array($row) && isset($row['name'], $row['status']) && strtolower((string) $row['status']) === 'approved') {
                $names[] = clean_param((string) $row['name'], PARAM_TEXT);
            }
        }
        return $names;
    }

    /**
     * SMS balance of the account.
     *
     * @return string
     * @throws \moodle_exception when the account cannot be read
     */
    public function balance(): string {
        [$code, $body, $error] = $this->request('POST', '/get-balance', []);
        if ($error !== '' || $code < 200 || $code >= 300 || !is_array($body)) {
            throw self::exception($code, $body, $error);
        }
        $balance = $body['data']['balance'] ?? $body['data']['Credit'] ?? $body['balance'] ?? '';
        return clean_param((string) $balance, PARAM_TEXT);
    }

    /**
     * One API request.
     *
     * @param string $method GET or POST
     * @param string $path path after the API address
     * @param array|null $body JSON body for POST
     * @return array{0: int, 1: mixed, 2: string} HTTP status, decoded body, and a connection error ('' when there was an answer)
     */
    private function request(string $method, string $path, ?array $body = null): array {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        if ($this->token === '') {
            return [0, null, get_string('notoken', 'smsgateway_corbit')];
        }
        if (!preg_match('/^[\x21-\x7E]+$/', $this->token)) {
            // A space or hidden character pasted with the token: refuse it before it reaches a request header.
            return [0, null, get_string('badtoken', 'smsgateway_corbit')];
        }
        $curl = new \curl();
        $curl->setHeader([
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->token,
        ]);
        // Short limits so a sign-in code or an admin page never waits long, and no redirects: the token must
        // not be sent on to another address.
        $options = [
            'CURLOPT_TIMEOUT' => 10,
            'CURLOPT_CONNECTTIMEOUT' => 5,
            'CURLOPT_FOLLOWLOCATION' => 0,
            'CURLOPT_MAXREDIRS' => 0,
        ];
        $url = self::base_url() . $path;
        if ($method === 'POST') {
            $response = $curl->post($url, json_encode($body ?? new \stdClass()), $options);
        } else {
            $response = $curl->get($url, [], $options);
        }
        $info = $curl->get_info();
        $code = (int) ($info['http_code'] ?? 0);
        if ($curl->get_errno() || $code === 0) {
            $error = trim((string) $curl->error);
            if ($error === '') {
                $error = \core_text::substr(trim((string) $response), 0, 200);
            }
            return [0, null, get_string('connectionerror', 'smsgateway_corbit', clean_param($error, PARAM_TEXT))];
        }
        if ($code >= 300 && $code < 400) {
            // A redirect is not followed (see above): report it instead of sending the token elsewhere.
            return [$code, null, get_string('errorhttp', 'smsgateway_corbit', $code)];
        }
        return [$code, json_decode((string) $response, true), ''];
    }

    /**
     * The exception for an account request that failed.
     *
     * @param int $code HTTP status
     * @param mixed $body decoded answer
     * @param string $error connection error, '' when there was an answer
     * @return \moodle_exception
     */
    private static function exception(int $code, $body, string $error): \moodle_exception {
        return new \moodle_exception('apierror', 'smsgateway_corbit', '', $error !== '' ? $error : self::error_text($code, $body));
    }

    /**
     * A short reason for a refused request.
     *
     * @param int $code HTTP status
     * @param mixed $body decoded answer
     * @return string
     */
    private static function error_text(int $code, $body): string {
        if ($code === 401) {
            return get_string('error401', 'smsgateway_corbit');
        }
        $text = '';
        if (is_array($body)) {
            foreach (['message', 'Message', 'error'] as $key) {
                if (!empty($body[$key]) && is_string($body[$key])) {
                    $text = trim(clean_param($body[$key], PARAM_TEXT));
                    break;
                }
            }
            // A validation answer lists the problem under the name of the field, such as number.
            if (!empty($body['errors']) && is_array($body['errors'])) {
                foreach ($body['errors'] as $field => $messages) {
                    $first = is_array($messages) ? reset($messages) : $messages;
                    if (!is_string($first) || trim($first) === '') {
                        continue;
                    }
                    $first = trim(clean_param($first, PARAM_TEXT));
                    if ($text === '') {
                        $text = clean_param((string) $field, PARAM_ALPHANUMEXT) . ': ' . $first;
                    } else if (!str_contains($text, $first)) {
                        $text = self::sentence($text) . ' ' . clean_param((string) $field, PARAM_ALPHANUMEXT) . ': ' . $first;
                    }
                    break;
                }
            }
        }
        if ($text === '') {
            $text = get_string('errorhttp', 'smsgateway_corbit', $code);
        }
        $hint = match (true) {
            $code === 402 => get_string('hint402', 'smsgateway_corbit'),
            $code === 422 => get_string('hint422', 'smsgateway_corbit'),
            $code === 429 => get_string('hint429', 'smsgateway_corbit'),
            $code >= 500 => get_string('hint500', 'smsgateway_corbit'),
            default => '',
        };
        return $hint === '' ? $text : self::sentence($text) . ' ' . $hint;
    }

    /**
     * Ends a server message with a full stop, so a hint can follow it.
     *
     * @param string $text
     * @return string
     */
    private static function sentence(string $text): string {
        return preg_match('/[.!?\x{061F}]$/u', $text) ? $text : $text . '.';
    }
}
