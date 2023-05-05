<?php

namespace App\Service;

use App\Entity\Bot;
use App\Entity\Survey;
use App\Entity\SurveyAccess;
use App\Entity\User;
use App\Enum\AccessProperty;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AccessesService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;
    }

    public function changeUserAccesses(Bot|Survey $entity, string $accessEntityClass, string $action, array $data): array
    {
        if ($action === 'update') {
            $access = $this->em->find($accessEntityClass, (int) $data['id']);
            $access->setRole($data['newRole']);

            $this->em->persist($access);
            $this->em->flush();

            return [];
        }

        // todo если респондент пользовался ботом, то просто добавить доступ по полю
        if ($action === 'add') {
            /** @var User $user */
            $user = $this->em->getRepository(User::class)->findOneBy(['email' => $data['email']]);

            if (null === $user) {
                throw new NotFoundHttpException('User not found');
            }

            $newAccess = (new $accessEntityClass())
                ->setRole($data['role']);

            $user->addAccess($newAccess);
            $entity->addUser($newAccess);

            $this->em->persist($newAccess);
            $this->em->flush();

            return [
                'newId' => $newAccess->getId(),
                'user' => [
                    'name' => $user->getLastAndFirstName(),
                ],
            ];
        }
        
        // todo оставить привязку того, что респондент пользовался ботом
        if ($action === 'remove') {
            $access = $this->em->find($accessEntityClass, (int) $data['id']);
            $user = $access->getUserData();

            $user->removeAccess($access);
            $entity->removeUser($access);
            $this->em->remove($access);

            $this->em->flush();
            return [];
        }
        
        throw new ConflictHttpException('Unsupported action');
    }

    public function changeRespondentsAccesses(Bot|Survey $entity, string $accessEntityClass, string $action, array $data): array
    {
        [$_, $action, $field] = explode('-', $action);
        
        if ($field === 'emails') {
            $propertyName = AccessProperty::EMAIL;
        } else if ($field === 'phones') {
            $propertyName = AccessProperty::PHONE;
        }

        $count = 0;

        if ($action === 'add') {
            if ($entity instanceof Survey) {
                $bot = $entity->getBot();

                foreach ($data['data'] as $item) {
                    $botAccess = $bot->getRespondentAccessBy($propertyName, $item);
                    if ($botAccess === null) {
                        continue;
                    }

                    $surveyAccess = $entity->getRespondentAccessBy($propertyName, $item);
                    if (null === $surveyAccess) {
                        $surveyAccess = (new $accessEntityClass())
                        ->setPropertyName($propertyName)
                        ->setPropertyValue($item);
                    }

                    // привязываем респондента к доступам опросов
                    if (null !== $botAccess->getRespondent()) {
                        $botAccess->getRespondent()->addSurveyAccess($surveyAccess);
                    }
                    $this->em->persist($surveyAccess);

                    $entity->addRespondentAccess($surveyAccess);

                    $this->em->persist($surveyAccess);
                    ++$count;
                }
            } else if ($entity instanceof Bot) {
                foreach ($data['data'] as $item) {
                    $access = $entity->getRespondentAccessBy($propertyName, $item);
                    if ($access !== null) {
                        continue;
                    }

                    $newAccess  = (new $accessEntityClass())
                        ->setPropertyName($propertyName)
                        ->setPropertyValue($item);
    
                    $entity->addRespondentAccess($newAccess);
    
                    $this->em->persist($newAccess);
                    ++$count;
                }
            }

            $this->em->flush();
            return ['changedCount' => $count];
        }

        if ($action === 'remove') {
            if ($entity instanceof Survey) {
                foreach ($data['data'] as $value) {
                    $access = $entity->getRespondentAccessBy($propertyName, $value);
                    if (null === $access) {
                        continue;
                    }
    
                    $this->em->remove($access);
                    ++$count;
                }
            } else if ($entity instanceof Bot) {
                foreach ($data['data'] as $value) {
                    $access = $entity->getRespondentAccessBy($propertyName, $value);
                    if (null === $access) {
                        continue;
                    }

                    $this->em->remove($access);

                    foreach ($entity->getSurveys() as $survey) {
                        $surveyAccess = $survey->getRespondentAccessBy($propertyName, $value);
                        if (null === $surveyAccess) {
                            continue;
                        }

                        $this->em->remove($surveyAccess);
                    }
    
                    ++$count;
                }
            }
            
            $this->em->flush();
            return ['changedCount' => $count];
        }

        throw new ConflictHttpException('Unsupported action');
    }
}
