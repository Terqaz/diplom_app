<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\BotRepository;
use App\Repository\SurveyRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/personal')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_show', methods: ['GET'])]
    public function show(): Response
    {
        return $this->render('user/show.html.twig', []);
    }

    #[Route('/bots', name: 'app_user_bot_index', methods: ['GET'])]
    public function botIndex(BotRepository $botRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('user/bots.html.twig', [
            'bots' => $botRepository->findByUserId($user->getId()),
        ]);
    }

    #[Route('/surveys', name: 'app_user_survey_index', methods: ['GET'])]
    public function surveyIndex(SurveyRepository $surveyRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('user/surveys.html.twig', [
            'surveys' => $surveyRepository->findByUserId($user->getId()),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($userPasswordHasher->isPasswordValid($user, $form->get('actualPassword')->getData())) {
                $newPassword = $form->get('newPassword')->getData();
                if (null !== $newPassword) {
                    $user->setPassword(
                        $userPasswordHasher->hashPassword($user, $newPassword)
                    );
                }

                $userRepository->save($user, true);
            } else {
                $form->get('actualPassword')->addError(new FormError('Укажите корректный пароль'));

                return $this->renderForm('user/edit.html.twig', [
                    'user' => $user,
                    'form' => $form,
                ]);
            }

            return $this->redirectToRoute('app_user_show', ['id' => $user->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
}
