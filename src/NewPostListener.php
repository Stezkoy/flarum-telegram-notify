<?php

namespace Stezkoy\TelegramNotify;

use Flarum\Http\UrlGenerator;
use Flarum\Post\Event\Posted;
use Flarum\Settings\SettingsRepositoryInterface;

class NewPostListener
{
    public function __construct(
        private readonly TelegramNotifier $notifier,
        private readonly UrlGenerator $url,
        private readonly SettingsRepositoryInterface $settings,
    ) {}

    public function handle(Posted $event): void
    {
        $post = $event->post;
        $discussion = $post->discussion;

        if ($discussion === null) {
            return;
        }

        if ($post->number === 1) {
            return;
        }

        $title = $discussion->title;

        $user = $post->user;
        $authorName = $user?->display_name ?? 'Unknown';

        $content = '';
        if (!empty($post->content)) {
            $content = strip_tags((string) $post->content);
        }
        $excerpt = mb_substr($content, 0, 200);
        if (mb_strlen($content) > 200) {
            $excerpt .= '…';
        }

        $discussionUrl = $this->url->to('forum')->route('discussion', ['id' => $discussion->id]);

        $message = TemplateRenderer::render(
            $this->template(),
            [
                '{title}' => TemplateRenderer::escape($title),
                '{author}' => TemplateRenderer::escape($authorName),
                '{excerpt}' => TemplateRenderer::escape($excerpt),
                '{url}' => $discussionUrl,
            ]
        );

        $this->notifier->dispatch($message);
    }

    private function template(): string
    {
        $template = $this->settings->get('stezkoy-telegram-notify.new_post_template');

        if (!is_string($template) || trim($template) === '') {
            return MessageTemplates::NEW_POST;
        }

        return $template;
    }
}
