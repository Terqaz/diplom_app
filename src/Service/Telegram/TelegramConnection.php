<?php

namespace Service\Telegram;

use App\Entity\Bot;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Service\BotConnectionInterface;

class TelegramConnection implements BotConnectionInterface
{
    private Bot $bot;
    private Telegram $api;

    private int $getUpdatesWay;

    public function __construct(Bot $bot, int $getUpdatesWay = self::USING_WEBHOOK)
    {
        $this->bot = $bot;
        $this->getUpdatesWay = $getUpdatesWay;

        if ($getUpdatesWay === self::USING_METHOD) {
            $this->settingGetUpdates($bot);
        } else if ($getUpdatesWay === self::USING_WEBHOOK) {
            // TODO
        }
    }

    public function getApi(): Telegram
    {
        return $this->api;
    }

    public static function getAllowedUpdates(): array
    {
        return [
            Update::TYPE_MESSAGE,
            // TODO
            // Update::TYPE_CALLBACK_QUERY
        ];
    }

    private function settingGetUpdates(): void
    {
        $settings = $this->bot->getTelegramNetwork();

        try {
            // Create Telegram API object
            $this->api = (new Telegram($settings->getAccessToken(), $settings->getConnectionId()))
                ->useGetUpdatesWithoutDatabase();

            // Handle telegram getUpdates request
            $this->api->handleGetUpdates(['allowed_updates' => self::getAllowedUpdates()]);
        } catch (TelegramException $e) {
            // log telegram errors
            // echo $e->getMessage();
        }
    }

    
}
