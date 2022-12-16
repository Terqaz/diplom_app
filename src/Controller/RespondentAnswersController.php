<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/respondent/answers')]
class RespondentAnswersController extends AbstractController
{
    #[Route('/', name: 'app_respondent_answers_search', methods: ['GET'])]
    public function search(): Response
    {
        return new Response('dev');
    }

    #[Route('/load', name: 'app_respondent_answers_load', methods: ['GET'])]
    public function loadAnswersFile(): Response
    {
        return new Response('dev');
    }

    #[Route('/statistics', name: 'app_respondent_answers_statistics_show', methods: ['GET'])]
    public function showStatistics(): Response
    {
        return new Response('dev');
    }

    #[Route('/statistics/load', name: 'app_respondent_answers_statistics_file_load', methods: ['GET'])]
    public function loadStatisticsFile(): Response
    {
        return new Response('dev');
    }
}
