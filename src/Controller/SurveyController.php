<?php

namespace App\Controller;

use App\Entity\Bot;
use App\Entity\Schedule;
use App\Entity\Survey;
use App\Entity\SurveyAccess;
use App\Entity\SurveyUser;
use App\Entity\User;
use App\Enum\AnswerValueType;
use App\Enum\QuestionType;
use App\Enum\UserRole;
use App\Form\DataTransformer\SurveyFormTransformer;
use App\Form\SurveyType;
use App\Repository\SurveyRepository;
use App\Service\AccessesService;
use App\Service\Front\EnumerationTranslator;
use App\Service\Front\MenuService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\Context\SerializerContextBuilder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/survey')]
class SurveyController extends AbstractController
{
    private NormalizerInterface $normalizer;
    private EnumerationTranslator $enumTranslator;
    private ValidatorInterface $validator;
    private EntityManagerInterface $em;

    public function __construct(NormalizerInterface $normalizer, EnumerationTranslator $enumTranslator, ValidatorInterface $validator, EntityManagerInterface $em)
    {
        $this->normalizer = $normalizer;
        $this->enumTranslator = $enumTranslator;
        $this->validator = $validator;
        $this->em = $em;
    }

    #[Route('/new', name: 'app_survey_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request): Response
    {
        /** @var ?User $user */
        $user = $this->getUser();
        $survey = new Survey();

        $botId = $request->query->get('bot_id');
        $bot = $this->em->find(Bot::class, (int) $botId);

        $form = $this->createForm(SurveyType::class, $survey, [
            'user_id' => $user->getId(),
            'bot' => $bot,
            'is_new' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyUser = (new SurveyUser())
                ->setRole(UserRole::QUESTIONER);

            $survey->addUser($surveyUser);
            $user->addSurveyUser($surveyUser);

            $this->em->persist($survey);
            $this->em->persist($user);
            $this->em->flush();

            return $this->redirectToRoute('app_user_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('survey/new.html.twig', [
            'survey' => $survey,
            'form' => $form,
            'botId' => $botId
        ]);
    }

    #[Route('/{id}', name: 'app_survey_show', methods: ['GET'], options: ['expose' => true])]
    public function show(Survey $survey, MenuService $menuService): Response
    {
        $userRole = $survey->getUserRole($this->getUser());

        if ($survey->isPrivate()) {
            if (!UserRole::isGranted($userRole, UserRole::VIEWER)) {
                return new RedirectResponse(
                    $this->generateUrl('app_login'),
                    Response::HTTP_SEE_OTHER
                );
            }
        }

        return $this->render('survey/show.html.twig', [
            'survey' => $survey,
            'menu' => $menuService->getSurveyMenuData($survey, $userRole)
        ]);
    }

    #[Route('/{id}/main-info/edit', name: 'app_survey_main_info_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editMainInfo(Survey $survey, Request $request): Response
    {
        /** @var ?User $user */
        $user = $this->getUser();

        $form = $this->createForm(SurveyType::class, $survey, [
            'user_id' => $user->getId(),
            'is_new' => false
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($survey);
            $this->em->flush();

            return $this->redirectToRoute('app_survey_show', ['id' => $survey->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('survey/main_info_edit.html.twig', [
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/schedule/edit', name: 'app_survey_schedule_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editSchedule(Survey $survey, Request $request, SerializerInterface $serializer): Response
    {
        if ($request->getMethod() === 'POST') {
            /** @var Survey $requestSurvey */
            $requestSurvey = $serializer->deserialize(
                $request->getContent(),
                Survey::class,
                'json'
            );

            $survey->setIsMultiple($requestSurvey->isMultiple());

            if (null !== $survey->getSchedule()) {
                $this->em->remove($survey->getSchedule());
                $survey->setSchedule(null);

                $this->em->flush();
            }

            if (null !== $requestSurvey->getSchedule()) {
                $survey->setSchedule($requestSurvey->getSchedule());
            }

            $this->em->persist($survey);
            $this->em->flush();

            return $this->redirectToRoute(
                'app_survey_show',
                ['id' => $survey->getId()],
                Response::HTTP_SEE_OTHER
            );
        }

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['surveyScheduleEdit'])
            ->withSkipNullValues(true)
            ->toArray();

        $surveyData = $this->normalizer->normalize($survey, null, $context);

        return $this->render('survey/edit_schedule.html.twig', [
            'surveyData' => $surveyData,
            'typesCatalogs' => [
                'scheduleTypes' => $this->enumTranslator->transByArray('schedule.types', Schedule::TYPES),
                'scheduleNoticeBeforeTypes' => $this->enumTranslator->transByValues('schedule.notice_before', 'value', [0, 1, 5, 10, 30, 60, 120]),
            ]
        ]);
    }

    #[Route('/{id}/form/edit', name: 'app_survey_form_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function editForm(Survey $survey, Request $request, SurveyFormTransformer $transformer): Response
    {
        if ($request->getMethod() === 'POST') {
            $newSurvey = $transformer->reverseTransform($request->getContent());
            $errors = $this->validator->validate($newSurvey);

            if (count($errors) > 0) {
                foreach ($errors as &$error) {
                    $error = $error->getMessage();
                }

                return new JsonResponse(['status' => false, 'errors' => $errors], Response::HTTP_BAD_REQUEST);
            }

            $survey
                ->setIsEmailRequired($newSurvey->isEmailRequired())
                ->setIsPhoneRequired($newSurvey->isPhoneRequired());

            // Удаляем старые элементы
            foreach ($survey->getQuestions() as $question) {
                $survey->removeQuestion($question);
                $this->em->remove($question);
            }

            foreach ($survey->getJumpConditions() as $jump) {
                $survey->removeJumpCondition($jump);
                $this->em->remove($jump);
            }

            $this->em->flush();

            // Добавляем новые
            foreach ($newSurvey->getQuestions() as $question) {
                $survey->addQuestion($question);
                $this->em->persist($question);
            }

            foreach ($newSurvey->getJumpConditions() as $jump) {
                $survey->addJumpCondition($jump);
                $this->em->persist($jump);
            }

            $this->em->flush();

            return new JsonResponse(['status' => true]);
        }

        $surveyData = $transformer->transform($survey);

        return $this->render('survey/edit_form.html.twig', [
            'surveyData' => $surveyData,
            'typesCatalogs' => [
                'question' => $this->enumTranslator->transByEnum('question.types', QuestionType::class),
                'answerValue' => $this->enumTranslator->transByEnum('answer_value.types', AnswerValueType::class),
            ]
        ]);
    }

    #[Route('/{id}/user-accesses/edit', name: 'app_survey_user_accesses_edit', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function userAccessesEdit(Survey $survey): Response
    {
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups(['accessesEdit', 'userAccessesEdit'])
            ->withSkipNullValues(true)
            ->toArray();

        $surveyData = $this->normalizer->normalize($survey, null, $context);
        $surveyData['type'] = 'survey';

        return $this->render('user_accesses_edit.html.twig', [
            'entityData' => $surveyData,
            'typesCatalogs' => [
                'userRole' => $this->enumTranslator->transByArray('user.roles', SurveyUser::ROLES),
            ],
        ]);
    }

    #[Route('/{id}/user-access/{action}', name: 'app_survey_user_access_change', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function changeAccess(Survey $survey, string $action, Request $request, DecoderInterface $decoder, AccessesService $accessesService): Response
    {
        try {
            if (!str_starts_with($action, 'respondents')) {
                $result = $accessesService->changeUserAccesses(
                    $survey,
                    SurveyUser::class,
                    $action,
                    $decoder->decode($request->getContent(), 'json')
                );
            } else {
                $result = $accessesService->changeRespondentsAccesses(
                    $survey,
                    SurveyAccess::class,
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

    #[Route('/{id}', name: 'app_survey_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $survey->getId(), $request->request->get('_token'))) {
            $surveyRepository->remove($survey, true);
        }

        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }
}
