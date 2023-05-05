<?php

namespace App\Service\Vk;

use App\Dto\BotUpdate;
use App\Entity\SocialNetworkConfig;
use App\Service\BotClient;
use Exception;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use VK\Client\Enums\VKLanguage;
use VK\Client\VKApiClient;
use VK\TransportClient\Curl\CurlHttpClient;

final class VkBotClient extends BotClient
{
    private const API_VERSION = '5.131';
    public const TEXT_BUTTON = 'text';

    private DecoderInterface $decoder;

    /**
     * @var array{key: string, server: string}|null
     */
    private ?array $longPollServer = null;

    private ?string $lastTs = null;

    private ?CurlHttpClient $curlHttpClient = null;

    public function __construct(SocialNetworkConfig $config)
    {
        parent::__construct($config, new VkKeyboardMaker());

        $this->decoder = new JsonEncoder();
        $this->api = new VKApiClient(self::API_VERSION, VKLanguage::RUSSIAN);
    }

    protected function getApi(): VKApiClient
    {
        return $this->api;
    }

    public function getKeyboardMaker(): VkKeyboardMaker
    {
        return $this->keyboardMaker;
    }

    public function getAllowedUpdates(): array
    {
        return [
            'message_new' => 1,
            'message_event' => 1 // нажатие на inline-кнопку
        ];
    }

    /**
     * @inheritDoc
     */
    public function getUpdates(): iterable
    {
        if (!$this->curlHttpClient) {
            $options = array_merge(
                [
                    'group_id' => (int) $this->config->getConnectionId(),
                    'enabled' => 1, // включить Bots Long Poll
                    'api_version' => self::API_VERSION
                ],
                $this->getAllowedUpdates()
            );

            $this->getApi()->groups()->setLongPollSettings(
                $this->config->getAccessToken(),
                $options
            );

            $this->longPollServer = $this->getApi()->groups()->getLongPollServer(
                $this->config->getAccessToken(),
                [
                    'group_id' => (int) $this->config->getConnectionId()
                ]
            );
            $this->lastTs = $this->longPollServer['ts'];

            $this->curlHttpClient = new CurlHttpClient(self::UPDATES_TIMEOUT);
        }

        $response = $this->curlHttpClient->get($this->longPollServer['server'], [
            'act' => 'a_check',
            'key' => $this->longPollServer['key'],
            'ts' => $this->lastTs,
            'wait' => self::UPDATES_TIMEOUT,
        ])->getBody();

        $response = $this->decoder->decode($response, 'json');

        if (!isset($response['updates'])) {
            return [];
        }
        
        $updates = $response['updates'];

        foreach ($updates as &$update) {
            $update = BotUpdate::fromVkUpdate($update);
        }

        $this->lastTs = $response['ts'];

        return $updates;
    }

    public function testConnection(): bool
    {
        try {
            $this->getApi()->groups()->getTokenPermissions(
                $this->config->getAccessToken()
            );
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function sendMessage(int|string $chatId, string $messageText, mixed $keyboard = null): int
    {
        $params = [
            'user_id' => (int) $chatId,
            'random_id' => random_int(0, 2 ** 31),
            'message' => $messageText
        ];

        if (null !== $keyboard) {
            $params['keyboard'] = (new JsonEncoder())->encode($keyboard, 'json');
        }

        return $this->getApi()->messages()->send(
            $this->config->getAccessToken(),
            $params
        ); // id сообщения
    }

    public function editMessage(int|string $chatId, int $messageId, ?string $messageText = null, mixed $keyboard = null): int
    {
        $params = [
            'peer_id' => (int) $chatId,
            'message_id' => (int) $messageId,
        ];

        if (null !== $messageText) {
            $params['message'] = $messageText;
        }

        if (null !== $keyboard) {
            $params['keyboard'] = (new JsonEncoder())->encode($keyboard, 'json');
        }

        return $this->getApi()->messages()->edit(
            $this->config->getAccessToken(),
            $params
        );
    }

    public function deleteMessage(int|string $chatId, int $messageId): bool
    {
        return (bool) $this->getApi()->messages()->delete(
            $this->config->getAccessToken(),
            [
                'message_ids' => [(int) $messageId],
                'peer_id' => (int) $chatId,
                'delete_for_all' => 1,
            ]
        );
    }

    public function answerCallbackQuery(string $callbackId, ?int $userId, ?int $dialogId): void
    {
        $this->getApi()->getRequest()->post(
            'messages.sendMessageEventAnswer',
            $this->config->getAccessToken(),
            [
                'event_id' => $callbackId,
                'user_id' => $userId,
                'peer_id' => $dialogId,
            ]
        );
    }
}
