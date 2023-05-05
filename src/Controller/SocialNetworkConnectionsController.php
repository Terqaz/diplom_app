<?php

namespace App\Controller;

use App\Entity\SocialNetworkConfig;
use App\Service\BotClient;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

class SocialNetworkConnectionsController extends AbstractController
{
    private EntityManagerInterface $em;
    private DecoderInterface $decoder;

    public function __construct(EntityManagerInterface $em, DecoderInterface $decoder)
    {
        $this->em = $em;
        $this->decoder = $decoder;
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

    
    #[Route('/connection/{code}/test', name: 'app_connection_test', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function testConnection(string $code, Request $request): JsonResponse
    {
        $data = $this->decoder->decode($request->getContent(), 'json');

        $config = (new SocialNetworkConfig())
            ->setCode($code)
            ->setAccessToken($data['accessToken']);

        $client = BotClient::createByCode($config);

        return new JsonResponse(['status' => $client->testConnection()]);
    }
}
