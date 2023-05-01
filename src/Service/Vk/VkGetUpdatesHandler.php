<?php

namespace App\Service\Vk;

use VK\CallbackApi\VKCallbackApiHandler;

class VkGetUpdatesHandler extends VKCallbackApiHandler 
{
    public function messageNew(int $groupId, ?string $secret, array $object) {
        echo 'New message: ' . $object['body'];
    }
}
