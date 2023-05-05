<?php

namespace App\Controller;

use App\Entity\Bot;
use App\Entity\BotAccess;
use App\Entity\BotUser;
use App\Entity\SocialNetworkConfig;
use App\Entity\User;
use App\Enum\AccessProperty;
use App\Enum\SocialNetworkCode;
use App\Enum\UserRole;
use App\Form\Bot\BotMainInfoType;
use App\Form\Bot\BotSocialNetworkConfigsType;
use App\Form\BotType;
use App\Repository\BotRepository;
use App\Repository\UserRepository;
use App\Service\AccessesService;
use App\Service\BotClient;
use App\Service\Front\EnumerationTranslator;
use App\Service\Front\MenuService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/bot')]
class BotController extends AbstractController
{
    private NormalizerInterface $normalizer;
    private EnumerationTranslator $enumTranslator;
    private SerializerInterface $serializer;

    public function __construct(NormalizerInterface $normalizer, EnumerationTranslator $enumTranslator, SerializerInterface $serializer)
    {
        $this->normalizer = $normalizer;
        $this->enumTranslator = $enumTranslator;
        $this->serializer = $serializer;
    }

    #[Route('/new', name: 'app_bot_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $bot = new Bot();
        $form = $this->createForm(BotMainInfoType::class, $bot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $botUser = (new BotUser())
                ->setRole(UserRole::ADMIN);

            /** @var User $user */
            $user = $this->getUser();
            $user->addBotUser($botUser);

            $bot->addUser($botUser);

            $em->persist($botUser);
            $em->persist($user);
            $em->persist($bot);
            $em->flush();

            return $this->redirectToRoute('app_user_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bot/new.html.twig', [
            'bot' => $bot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bot_show', methods: ['GET'])]
    public function show(Bot $bot, MenuService $menuService): Response
    {
        /** @var ?User $user */
        $user = $this->getUser();
        $userRole = $bot->getUserRole($user);

        /** @var ArrayCollection $availableSurveys */
        $availableSurveys = $bot->getSurveys();

        if ($bot->isPrivate()) {
            if (!UserRole::isGranted($userRole, UserRole::VIEWER)) {
                return new RedirectResponse(
                    $this->generateUrl('app_login'),
                    Response::HTTP_SEE_OTHER
                );
            }
        } else {
            if (!UserRole::isGranted($userRole, UserRole::VIEWER)) {
                // Только публичные опросы
                $availableSurveys = $availableSurveys->matching(
                    Criteria::create()->where(Criteria::expr()->eq('isPrivate', false))
                );
            }
        }

        return $this->render('bot/show.html.twig', [
            'bot' => $bot,
            'surveys' => $availableSurveys,
            'connections' => SocialNetworkCode::TYPES,
            'menu' => $menuService->getBotMenuData($bot, $userRole)
        ]);
    }

    #[Route('/{id}/user-accesses/edit', name: 'app_bot_user_accesses_edit', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userAccessesEdit(Bot $bot): Response
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['accessesEdit', 'userAccessesEdit'])
            ->withSkipNullValues(true)
            ->toArray();

        $botData = $this->normalizer->normalize($bot, null, $context);
        $botData['type'] = 'bot';

        return $this->render('user_accesses_edit.html.twig', [
            'entityData' => $botData,
            'typesCatalogs' => [
                'userRole' => $this->enumTranslator->transByArray('user.roles', BotUser::ROLES),
            ],
        ]);
    }

    #[Route('/{id}/user-access/{action}', name: 'app_bot_user_access_change', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function changeAccess(Bot $bot, string $action, Request $request, DecoderInterface $decoder, AccessesService $accessesService): Response
    {
        try {
            if (!str_starts_with($action, 'respondents')) {
                $result = $accessesService->changeUserAccesses(
                    $bot,
                    BotUser::class,
                    $action,
                    $decoder->decode($request->getContent(), 'json')
                );
            } else {
                $result = $accessesService->changeRespondentsAccesses(
                    $bot,
                    BotAccess::class,
                    $action,
                    $decoder->decode($request->getContent(), 'json')
                );
            }

            $result['status'] = true;

            return new JsonResponse($result);
        } catch (HttpException $e) {
            return new JsonResponse(['status' => false, 'errors' => [$e->getMessage()]], $e->getStatusCode());
        }
    }

    #[Route('/{id}/main-info/edit', name: 'app_bot_main_info_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editMainInfo(Request $request, Bot $bot, BotRepository $botRepository): Response
    {
        $form = $this->createForm(BotMainInfoType::class, $bot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $botRepository->save($bot, true);

            return $this->redirectToRoute('app_bot_show', ['id' => $bot->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('bot/main_info_edit.html.twig', [
            'bot' => $bot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/connections/edit', name: 'app_bot_connections_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editConnections(Bot $bot, Request $request, ValidatorInterface $validator, EntityManagerInterface $em): Response
    {
        if ($request->getMethod() === 'POST') {
            $configs = $this->serializer->deserialize(
                $request->getContent(),
                SocialNetworkConfig::class . '[]',
                'json'
            );

            foreach ($configs as $config) {
                $errors = $validator->validate($config);

                if (count($errors) > 0) {
                    foreach ($errors as &$error) {
                        $error = $error->getMessage();
                    }

                    return new JsonResponse(['status' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
                }
            }

            foreach ($bot->getSocialNetworkConfigs() as $oldConfig) {
                $bot->removeSocialNetworkConfig($oldConfig);
                $em->remove($oldConfig);
            }

            foreach ($configs as $config) {
                $bot->addSocialNetworkConfig($config);
            }

            $em->persist($bot);
            $em->flush();

            return $this->redirectToRoute(
                'app_bot_show',
                ['id' => $bot->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['connectionsEdit'])
            ->withSkipNullValues(true)
            ->toArray();

        $configsData = [];

        foreach ($bot->getSocialNetworkConfigs() as $config) {
            $configsData[$config->getCode()] = $this->normalizer->normalize($config, null, $context);
        }

        return $this->render('bot/social_network_configs_edit.html.twig', [
            'bot' => $bot,
            'configsData' => $configsData,
            'typesCatalogs' => [
                'socialNetworkCodes' => $this->enumTranslator->transByEnum('social_network.codes', SocialNetworkCode::class),
            ]
        ]);
    }
}
