<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SocialNetworkBotController extends AbstractController
{
    #[Route('/webhook/', name: 'app_webhook')]
    public function handleWebhooks(): Response
    {
        return new Response('dev');
    }
}
