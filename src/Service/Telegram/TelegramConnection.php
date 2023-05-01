<?php

namespace App\Service\Telegram;

use App\Entity\Bot;
use App\Entity\SocialNetwork;
use App\Service\BotConnectionInterface;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;

class TelegramConnection implements BotConnectionInterface
{
    private Bot $bot;
    private Telegram $client;

    private bool $isListening;
    private int $getUpdatesWay;

    public function __construct(Bot $bot, int $getUpdatesWay = SocialNetwork::WEBHOOK_UPDATES)
    {
        $this->bot = $bot;
        $this->getUpdatesWay = $getUpdatesWay;

        if ($getUpdatesWay === SocialNetwork::METHOD_UPDATES) {
            $this->settingGetUpdates();
        } else if ($getUpdatesWay === SocialNetwork::WEBHOOK_UPDATES) {
            $this->initCallback();
        }
    }

    private function settingGetUpdates(): void
    {
        $network = $this->bot->getTelegram();

        try {
            $this->client = (new Telegram(
                $network->getAccessToken(),
                $network->getConnectionId()
            ))
                ->useGetUpdatesWithoutDatabase();
        } catch (TelegramException $e) {
            // echo $e->getMessage();
        }
    }

    private function initCallback(): void
    {
        // TODO
    }

    public function startListening(): void
    {
        if ($this->isListening()) {
            return;
        }

        // TODO
        // $this->isListening = true;
        // Handle telegram getUpdates request
        $response = $this->client->handleGetUpdates(['allowed_updates' => self::getAllowedUpdates()]);

        if (!$response->isOk()) {
            $update_count = count($response->getResult());
            echo date('Y-m-d H:i:s') . ' - Processed ' . $update_count . ' updates';
        } else {
            echo date('Y-m-d H:i:s') . ' - Failed to fetch updates' . PHP_EOL;
            echo $response->printError();
        }
    }

    public function stopListening(): void
    {
        if (!$this->isListening()) {
            return;
        }

        $this->isListening = false;
        // TODO
    }

    public function isListening(): bool {
        return $this->isListening;
    }
    
    public function getBot(): Bot
    {
        return $this->bot;
    }

    public function getClient(): Telegram
    {
        return $this->client;
    }

    public static function getAllowedUpdates(): array
    {
        return [
            Update::TYPE_MESSAGE,
            // TODO
            // Update::TYPE_CALLBACK_QUERY
        ];
    }
}
