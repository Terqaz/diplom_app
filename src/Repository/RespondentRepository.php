<?php

namespace App\Repository;

use App\Entity\Respondent;
use App\Enum\SocialNetworkCode;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Respondent>
 *
 * @method Respondent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Respondent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Respondent[]    findAll()
 * @method Respondent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RespondentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Respondent::class);
    }

    public function save(Respondent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Respondent $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByUpdate(string $networkCode, int $fromId): ?Respondent
    {
        $propertyByCode = [
            SocialNetworkCode::TELEGRAM => 'telegramId',
            SocialNetworkCode::VKONTAKTE => 'vkontakteId'
        ];

        return $this->findOneBy([$propertyByCode[$networkCode] => $fromId]);
    }

    /**
     * Получить респондентов, использующих бота в определенной соц сети
     *
     * @param integer $botId
     * @param string $socialNetworkCode
     * @return array<Respondent>
     */
    public function findByBotUsed(int $botId, string $socialNetworkCode): array
    {
        $idField = Respondent::SOCIAL_NETWORK_ID_FIELD[$socialNetworkCode];

        return $this->createQueryBuilder('r')
            ->join('r.botAccesses', 'ba')
            ->join('ba.bot', 'b', 'WITH', 'b.id = :botId')
            ->where('r.' . $idField . ' IS NOT NULL')
            ->setParameter('botId', $botId)
            ->getQuery()
            ->getResult()
        ;
    }
}
