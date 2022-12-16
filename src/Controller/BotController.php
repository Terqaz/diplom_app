<?php

namespace App\Controller;

use App\Entity\Bot;
use App\Form\BotType;
use App\Repository\BotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bot')]
class BotController extends AbstractController
{
    #[Route('/search', name: 'app_bot_search', methods: ['GET'])]
    public function search(BotRepository $botRepository): Response
    {
        return new Response('dev');
    }

    #[Route('/new', name: 'app_bot_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BotRepository $botRepository): Response
    {
        $bot = new Bot();
        $form = $this->createForm(BotType::class, $bot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $botRepository->save($bot, true);

            return $this->redirectToRoute('app_bot_show', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bot/new.html.twig', [
            'bot' => $bot,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bot_show', methods: ['GET'])]
    public function show(Bot $bot): Response
    {
        return $this->render('bot/show.html.twig', [
            'bot' => $bot,
        ]);
    }

    #[Route('/{id}/survey/add}', name: 'app_bot_survey_add', methods: ['POST'])]
    public function addSurvey(BotRepository $botRepository): Response
    {
        return new Response('dev');
    }

    #[Route('/{id}/survey/remove}', name: 'app_bot_survey_remove', methods: ['POST'])]
    public function removeSurvey(BotRepository $botRepository): Response
    {
        return new Response('dev');
    }

    #[Route('/{id}/user/add}', name: 'app_bot_user_add', methods: ['POST'])]
    public function addUser(BotRepository $botRepository): Response
    {
        return new Response('dev');
    }

    #[Route('/{id}/user/remove}', name: 'app_bot_user_remove', methods: ['POST'])]
    public function removeUser(BotRepository $botRepository): Response
    {
        return new Response('dev');
    }

    #[Route('/{id}/edit', name: 'app_bot_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Bot $bot, BotRepository $botRepository): Response
    {
        $form = $this->createForm(BotType::class, $bot);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $botRepository->save($bot, true);

            return $this->redirectToRoute('app_bot_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bot/edit.html.twig', [
            'bot' => $bot,
            'form' => $form,
        ]);
    }

//    #[Route('/{id}', name: 'app_bot_delete', methods: ['POST'])]
//    public function delete(Request $request, Bot $bot, BotRepository $botRepository): Response
//    {
//        if ($this->isCsrfTokenValid('delete'.$bot->getId(), $request->request->get('_token'))) {
//            $botRepository->remove($bot, true);
//        }
//
//        return $this->redirectToRoute('app_bot_index', [], Response::HTTP_SEE_OTHER);
//    }
}
