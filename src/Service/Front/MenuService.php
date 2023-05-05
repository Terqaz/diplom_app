<?php

namespace App\Service\Front;

use App\Dto\Front\MenuLink;
use App\Entity\Bot;
use App\Entity\Survey;
use App\Enum\UserRole;
use Symfony\Contracts\Translation\TranslatorInterface;

class MenuService
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getUserMenuData(): array
    {
        return self::asArray(
            titleText: $this->translator->trans('menu.title.user'),
            buttons: [
                new MenuLink(
                    code: 'common.new_bot',
                    routeName: 'app_bot_new'
                ),
                new MenuLink(
                    code: 'common.new_survey',
                    routeName: 'app_survey_new'
                ),
            ]
        );
    }

    public function getBotMenuData(Bot $bot, string $userRole): array
    {
        $buttons = [];

        if (UserRole::isGranted($userRole, UserRole::AUTHORIZED)) {
            $buttons[] = new MenuLink(
                code: 'common.new_bot',
                routeName: 'app_bot_new'
            );
        }

        if (UserRole::isGranted($userRole, UserRole::QUESTIONER)) {
            $buttons[] = new MenuLink(
                code: 'common.new_survey',
                routeName: 'app_survey_new',
                queryParams: ['bot_id' => $bot->getId()],
            );
        }

        return self::asArray(
            titleText: $this->translator->trans('menu.title.bot', [
                'title' => $bot->getTitle()
            ]),
            buttons: $buttons
        );
    }

    public function getSurveyMenuData(Survey $survey, string $userRole): array
    {
        $buttons = [];

        if (UserRole::isGranted($userRole, UserRole::QUESTIONER)) {
            $buttons[] = new MenuLink(
                code: 'common.new_survey',
                routeName: 'app_survey_new'
            );
        }

        $links = [];

        if (UserRole::isGranted($userRole, UserRole::VIEWER)) {
            $links[] = new MenuLink(
                code: 'survey.show',
                routeName: 'app_survey_show'
            );
        }

        if (UserRole::isGranted($userRole, UserRole::QUESTIONER)) {
            $links[] = new MenuLink(
                code: 'survey.edit_form',
                routeName: 'app_survey_form_edit'
            );
        }

        $links[] = new MenuLink(
            code: 'survey.answers',
            routeName: 'app_survey_answer_search'
        );

        if (UserRole::isGranted($userRole, UserRole::VIEWER)) {
            $links[] = new MenuLink(
                code: 'survey.statistics',
                routeName: 'app_survey_show_statistics'
            );
        }

        return self::asArray(
            titleText: $this->translator->trans('menu.title.survey', [
                'title' => $survey->getTitle()
            ]),
            links: [
                'entityId' => $survey->getId(),
                'list' => $links
            ],
            buttons: $buttons,
            options: [
                'botId' => $survey->getBot()->getId()
            ]
        );
    }

    /**
     * @param string $titleText название раздела
     * @param array $links ссылки на другие страницы для той же сущности
     * @param array $buttons кнопки действий
     * @return array
     */
    private static function asArray(string $titleText, ?array $links = null, array $buttons = [], ?array $options = null): array
    {
        $data = [
            'titleText' => $titleText,
            'buttons' => $buttons
        ];

        if (null !== $links) {
            $data['links'] = $links;
        }

        if (null !== $options) {
            $data['options'] = $options;
        }

        return $data;
    }
}
