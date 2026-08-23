<?php

namespace Stezkoy\FlarumTelegramNotify;

use Flarum\Discussion\Event\Started;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;

class NewDiscussionListener
{
    public function __construct(
        private readonly TelegramNotifier $notifier,
        private readonly UrlGenerator $url,
        private readonly SettingsRepositoryInterface $settings,
    ) {}

    public function handle(Started $event): void
    {
        $discussion = $event->discussion;
        $post = $event->post;

        $title = $discussion->title;

        $user = $discussion->user;
        $authorName = $user?->display_name ?? 'Unknown';

        $content = '';
        $sourcePost = $post;
        if ($sourcePost === null || empty($sourcePost->content)) {
            $sourcePost = $discussion->firstPost;
        }
        if ($sourcePost !== null && !empty($sourcePost->content)) {
            if (is_array($sourcePost->content)) {
                $pieces = [];
                foreach ($sourcePost->content as $block) {
                    if (is_array($block) && isset($block['text'])) {
                        $pieces[] = $block['text'];
                    }
                }
                $content = implode(' ', $pieces);
            } else {
                $content = (string) $sourcePost->content;
            }
        }

        $excerpt = TemplateRenderer::excerpt($content);

        $tagsString = TemplateRenderer::discussionTags($discussion);

        $discussionUrl = $this->url->to('forum')->route('discussion', ['id' => $discussion->id]);

        $message = TemplateRenderer::render(
            $this->template(),
            [
                '{title}' => TemplateRenderer::escape($title),
                '{tags}' => TemplateRenderer::escape($tagsString),
                '{author}' => TemplateRenderer::escape($authorName),
                '{excerpt}' => TemplateRenderer::escape($excerpt),
                '{url}' => $discussionUrl,
            ]
        );

        $this->notifier->dispatch($message);
    }

    private function template(): string
    {
        $template = $this->settings->get('stezkoy-flarum-telegram-notify.new_discussion_template');

        if (!is_string($template) || trim($template) === '') {
            return MessageTemplates::NEW_DISCUSSION;
        }

        return $template;
    }
}
