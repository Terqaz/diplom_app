<?php

namespace App\Controller;

use App\Dto\LoadFileSettings;
use App\Entity\AnswerVariant;
use App\Entity\Survey;
use App\Form\LoadFileSettingsType;
use App\Form\RespondentAnswer\ChartFilterType;
use App\Form\Survey\Filter\RespondentFormFilter;
use App\Form\Survey\RespondentFormFilterType;
use App\Repository\QuestionRepository;
use App\Repository\RespondentAnswerRepository;
use App\Service\ChartsService;
use App\Service\Front\MenuService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;

#[Route('/survey')]
class AnswerController extends AbstractController
{
    private MenuService $menuService;
    private FormFactoryInterface $formFactory;

    public function __construct(MenuService $menuService, FormFactoryInterface $formFactory)
    {
        $this->menuService = $menuService;
        $this->formFactory = $formFactory;
    }

    #[Route('/{id}/answers', name: 'app_survey_answer_search', methods: ['GET'])]
    public function answerSearch(Survey $survey, Request $request, RespondentAnswerRepository $respondentAnswerRepository, Pdf $knpSnappyPdf, EncoderInterface $encoder): Response
    {
        $filter = new RespondentFormFilter($survey);
        $filterForm = $this->formFactory->createNamed(
            'filter',
            RespondentFormFilterType::class,
            $filter,
            [
                'method' => 'GET',
                'is_phone_required' => $survey->isPhoneRequired(),
                'is_email_required' => $survey->isEmailRequired(),
            ]
        );

        $filterForm->handleRequest($request);

        $forms = $respondentAnswerRepository->findByFilter($survey, $filter);
        
        if ($filterForm->get('loadFile')->isClicked()) {
            if ($filter->getFileFormat() === RespondentFormFilter::PDF) {
                $knpSnappyPdf->setOption('encoding', 'utf-8');
                return new PdfResponse(
                    $knpSnappyPdf->getOutputFromHtml($this->renderView('pdf/respondent_answers.html.twig', [
                        'survey' => $survey,
                        'filter' => $filter,
                        'forms' => $forms
                    ])),
                    'Respondent forms.pdf',
                );
            }

            $content = [];

            foreach ($filter->getAnswers() as $answerFilter) {
                $content['questions'][] = [
                    'number' => $answerFilter->getQuestionNumber(),
                    'title' => $survey->getQuestionByNumber($answerFilter->getQuestionFormNumber())->getTitle(),
                    'enabled' => $answerFilter->isShow()
                ];
            }

            $content['answers'] = $forms;
            
            $response = new Response($encoder->encode($content, ($filter->getFileFormat())));

            $disposition = HeaderUtils::makeDisposition(
                HeaderUtils::DISPOSITION_ATTACHMENT,
                'Respondent forms.' . $filter->getFileFormat()
            );

            $response->headers->set('Content-Disposition', $disposition);
            $response->headers->set('Content-Type', 'text/plain');

            return $response;
        }

        return $this->render('survey/answer_search.html.twig', [
            'menu' => $this->menuService->getSurveyMenuData($survey, $survey->getUserRole($this->getUser())),
            'survey' => $survey,
            'filter' => $filter,
            'filterForm' => $filterForm,
            'forms' => $forms
        ]);
    }

    #[Route('/{id}/statistics', name: 'app_survey_show_statistics', methods: ['GET'])]
    public function showStatistics(Request $request, Survey $survey, ChartsService $chartsService): Response
    {
        $filter = $this->formFactory->createNamed('filter', ChartFilterType::class, null, [
            'method' => 'GET'
        ]);

        $filter->handleRequest($request);

        $period = $filter->isSubmitted() ? $filter->get('rfPeriod')->getData() : 30;
        $formsByTimeChart = $chartsService->createFormsByTimeChart($survey, $period);

        $answersCountCharts = [];
        foreach ($survey->getQuestions() as $question) {
            $answersCountCharts[$question->getTitle()] = $chartsService->createAnswersCountChart($question);
        }

        return $this->render('survey/statistics.html.twig', [
            'menu' => $this->menuService->getSurveyMenuData($survey, $survey->getUserRole($this->getUser())),
            'chart' => $formsByTimeChart,
            'filter' => $filter,
            'answersCountCharts' => $answersCountCharts,
        ]);
    }
}
