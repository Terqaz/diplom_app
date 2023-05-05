<?php

namespace App\Controller;

use App\Entity\SocialNetworkConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SocialNetworkConfigBotController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route(
        '/webhook/{connectionPath}',
        name: 'app_webhook',
        requirements: ['connectionPath' => '[\da-z]{10}']
    )]
    public function handleWebhooks(string $connectionPath): Response
    {
        $connection = $this->em->getRepository(SocialNetworkConfig::class)
            ->findOneBy(['webhookUrl' => '/webhook/' . $connectionPath]);

        if (null === $connection) {
            // TODO
        }



        return new Response('dev');
    }
}
