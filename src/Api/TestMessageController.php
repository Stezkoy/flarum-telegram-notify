<?php

namespace Stezkoy\FlarumTelegramNotify\Api;

use Flarum\Http\RequestUtil;
use Flarum\Http\UrlGenerator;
use Flarum\Locale\Translator;
use Flarum\Settings\SettingsRepositoryInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Stezkoy\FlarumTelegramNotify\MessageTemplates;
use Stezkoy\FlarumTelegramNotify\TemplateRenderer;
use Stezkoy\FlarumTelegramNotify\TelegramNotifier;

class TestMessageController implements RequestHandlerInterface
{
    public function __construct(
        private readonly TelegramNotifier $notifier,
        private readonly SettingsRepositoryInterface $settings,
        private readonly UrlGenerator $url,
        private readonly Translator $translator,
    ) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $actor = RequestUtil::getActor($request);
        $actor->assertAdmin();

        $template = (string) $this->settings->get('stezkoy-flarum-telegram-notify.new_post_template');

        if (trim($template) === '') {
            $template = MessageTemplates::NEW_POST;
        }

        $placeholders = [
            '{title}' => TemplateRenderer::escape((string) $this->translator->trans('stezkoy-flarum-telegram-notify.admin.test_sample_title')),
            '{author}' => TemplateRenderer::escape($actor->display_name ?? $actor->username ?? 'Admin'),
            '{excerpt}' => TemplateRenderer::escape((string) $this->translator->trans('stezkoy-flarum-telegram-notify.admin.test_sample_excerpt')),
            '{tags}' => '#test',
            '{url}' => $this->url->to('forum')->base(),
        ];

        return new JsonResponse(
            $this->notifier->send(TemplateRenderer::render($template, $placeholders))
        );
    }
}
