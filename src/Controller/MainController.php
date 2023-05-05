<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\BotRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/about', name: 'app_main')]
    public function about(): Response
    {
        return $this->render('about.html.twig', []);
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request, BotRepository $botRepository): Response
    {
        /** @var ?User $user */
        $user = $this->getUser();

        $query = mb_strtolower(trim($request->query->get('q')));

        $qb = $botRepository->createQueryBuilder('b');
        $qb->select('b, s')
            ->join('b.users', 'bu', 'WITH', 'b.isPrivate = FALSE OR (b.isPrivate = TRUE AND bu.userData = :userId)')
            ->join('b.surveys', 's')
            ->join('s.users', 'su', 'WITH', 's.isPrivate = FALSE OR (s.isPrivate = TRUE AND su.userData = :userId)')
            ->where('LOWER(b.title) LIKE :query')
            ->orWhere('LOWER(s.title) LIKE :query')

            ->setParameter('userId', $user?->getId() ?? -1)
            ->setParameter('query', '%' . $query . '%');

        $bots = $qb
            ->orderBy('b.title')
            ->addOrderBy('s.title')
            ->getQuery()
            ->getResult();

        return $this->render('search/bots_and_surveys.html.twig', [
            'bots' => $bots
        ]);
    }
}
