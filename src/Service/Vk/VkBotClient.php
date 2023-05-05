<?php

namespace App\Service\Vk;

use App\Service;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use VK\Client\Enums\VKLanguage;
use VK\Client\VKApiClient;

class VkBotClient extends Service\BotClient
{
    public const TEXT_BUTTON = 'text';

    private string $accessToken;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;

        $this->api = new VKApiClient('5.131', VKLanguage::RUSSIAN); //todo вынести версию
    }

    protected function getApi(): VKApiClient
    {
        return $this->api;
    }

    public function getUpdates(int $offset = null): array
    {
        throw new \Exception("Error statement");
    }

    public function sendMessage(int|string $chatId, string $messageText, ?array $keyboard = null): void
    {
        $params = [
            'user_id' => (int) $chatId,
            'random_id' => random_int(0, 2 ** 31),
        ];

        if (null !== $keyboard) {
            $params['keyboard'] = self::toVkTextKeyboard($keyboard);
        }

        $this->getApi()->messages()->send(
            $this->accessToken,
            $params
        );
    }

    private static function toVkTextKeyboard(array $keyboard): string
    {
        $mappedKeyboard = [];

        foreach ($keyboard as $row) {
            $mappedRow = [];

            foreach ($row as $text) {
                $mappedRow[] = [
                    'action' => [
                        'type' => self::TEXT_BUTTON,
                        'label' => $text
                    ]
                ];
            }
            $mappedKeyboard[] = $mappedRow;
        }

        return (new JsonEncoder())->encode($mappedKeyboard, 'json');
    }
}
