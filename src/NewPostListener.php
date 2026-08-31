<?php

namespace Stezkoy\FlarumTelegramNotify;

use Flarum\Http\UrlGenerator;
use Flarum\Post\Event\Posted;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;

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

        if (!$this->shouldNotify($discussion)) {
            return;
        }

        $title = $discussion->title;

        $user = $post->user;
        $authorName = $user?->display_name ?? 'Unknown';

        $excerpt = TemplateRenderer::excerpt($post->content);

        $discussionUrl = $this->url->to('forum')->route('discussion', ['id' => $discussion->id]);

        $message = TemplateRenderer::render(
            $this->template(),
            [
                '{title}' => TemplateRenderer::escape($title),
                '{tags}' => TemplateRenderer::escape(TemplateRenderer::discussionTags($discussion)),
                '{author}' => TemplateRenderer::escape($authorName),
                '{excerpt}' => TemplateRenderer::escape($excerpt),
                '{url}' => $discussionUrl,
            ]
        );

        $this->notifier->dispatch($message);
    }

    private function shouldNotify($discussion): bool
    {
        $enabledTagIds = json_decode(
            (string) $this->settings->get('stezkoy-telegram-notify.enabled-tags', '[]'),
            true
        );

        if (!is_array($enabledTagIds) || $enabledTagIds === []) {
            return true;
        }

        try {
            $tagIds = Arr::pluck($discussion->tags, 'id');
        } catch (\Throwable $e) {
            // flarum-tags extension not available — no filtering
            return true;
        }

        return array_intersect($enabledTagIds, $tagIds) !== [];
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
