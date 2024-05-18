<?php

namespace App\Helpers;

/**
 * TELEGRAM DOCS:
 * webApp => https://core.telegram.org/bots/webapps#validating-data-received-via-the-mini-app
 * login widget => https://core.telegram.org/widgets/login (checking authorization section)
 * */
class TelegramInitDataValidator
{
    private static function generateHash(string $botToken, string $data, bool $isWebApp = true): string {

        $secretKey = $isWebApp ? hash_hmac('sha256', $botToken, 'WebAppData', true) : hash('sha256', $botToken, true);

        $hash = hash_hmac('sha256', $data, $secretKey);
        return $hash;
    }

    /**
     * validate initData to ensure that it is from Telegram.
     *
     * @param string $botToken your bot token
     * @param string $initData init data from Telegram (`Telegram.WebApp.initData`)
     *
     * @return bool return true if its from Telegram otherwise false
     */
    public static function isSafe(string $botToken, string $initData): bool
    {
        [$checksum, $sortedInitData, $isWebApp] = self::convertInitData($initData);

        $hash = self::generateHash($botToken, $sortedInitData, $isWebApp);

        return $hash === $checksum;
    }

    /**
     * convert init data to `key=value` and sort it `alphabetically`.
     *
     * @param string $initData init data from Telegram (`Telegram.WebApp.initData`)
     *
     * @return array{string, string, bool} return hash and sorted init data
     */
    private static function convertInitData(string $initData): array
    {
        $initDataArray = explode('&', rawurldecode($initData));
        $needle = 'hash=';
        $hash = '';

        foreach ($initDataArray as &$data) {
            if (substr($data, 0, \strlen($needle)) === $needle) {
                $hash = substr_replace($data, '', 0, \strlen($needle));
                $data = null;
            }
        }

        $initDataArray = array_filter($initDataArray);
        sort($initDataArray);

        $isWebApp = strpos($initData, 'user=') ? true : false;

        return [$hash, implode("\n", $initDataArray), $isWebApp];
    }

    /**
     * Generates telegram init data with checksum.
     *
     * @param array{id: int, first_name: string, last_name: string} $user
     *
     * @return array<string, string>
     */
    public static function generateInitData(string $botToken, int $authDate, array $user): array
    {
        $initData = [
            'auth_date' => (string) $authDate,
            'user' => type(json_encode($user))->asString(),
        ];

        $dataCheckString = collect($initData)
            ->sort()
            ->map(fn ($value, $key) => "$key=$value")
            ->join("\n");

        $initData['hash'] = self::generateHash($botToken, $dataCheckString);

        return $initData;
    }
}
