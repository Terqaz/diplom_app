<?php

namespace App\Controller;

use App\Entity\Survey;
use App\Form\SurveyType;
use App\Repository\SurveyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/survey')]
class SurveyController extends AbstractController
{
    #[Route('/search', name: 'app_survey_search', methods: ['GET'])]
    public function search(): Response
    {
        return new Response('dev');
    }

    #[Route('/new', name: 'app_survey_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, SurveyRepository $surveyRepository): Response
    {
        $survey = new Survey();
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyRepository->save($survey, true);

            return $this->redirectToRoute('app_survey_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('survey/new.html.twig', [
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_survey_show', methods: ['GET'])]
    public function show(Survey $survey): Response
    {
        return $this->render('survey/show.html.twig', [
            'survey' => $survey,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_survey_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        $form = $this->createForm(SurveyType::class, $survey);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $surveyRepository->save($survey, true);

            return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('survey/edit.html.twig', [
            'survey' => $survey,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/dialog/edit', name: 'app_survey_dialog_edit', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function editDialog(): Response
    {
        return new Response('dev');
    }

    #[Route('/{id}', name: 'app_survey_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Survey $survey, SurveyRepository $surveyRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$survey->getId(), $request->request->get('_token'))) {
            $surveyRepository->remove($survey, true);
        }

        return $this->redirectToRoute('app_survey_index', [], Response::HTTP_SEE_OTHER);
    }
}
