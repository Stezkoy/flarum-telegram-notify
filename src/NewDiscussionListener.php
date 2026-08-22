<?php

namespace Stezkoy\NouiTeleNotify;

use Flarum\Discussion\Event\Started;
use Flarum\Http\UrlGenerator;
use Flarum\Locale\Translator;

class NewDiscussionListener
{
    public function __construct(
        private readonly TelegramNotifier $notifier,
        private readonly UrlGenerator $url,
        private readonly Translator $translator,
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
                $content = strip_tags((string) $sourcePost->content);
            }
        }
        $excerpt = mb_substr($content, 0, 200);
        if (mb_strlen($content) > 200) {
            $excerpt .= '…';
        }

        $tagsString = '';
        try {
            $tags = $discussion->tags;
            if ($tags !== null && $tags->isNotEmpty()) {
                $tagNames = $tags->pluck('name')->map(fn($name) => '#' . $name);
                $tagsString = $tagNames->implode(' ');
            }
        } catch (\Throwable $e) {
            // tags extension not available
        }

        $discussionUrl = $this->url->to('forum')->route('discussion', ['id' => $discussion->id]);

        $message = $this->translator->trans('stezkoy-noui-tele-notify.forum.new_discussion', [
            '{title}' => $this->escape($title),
            '{tags}' => $this->escape($tagsString),
            '{author}' => $this->escape($authorName),
            '{excerpt}' => '<i>' . $this->escape($excerpt) . '</i>',
            '{url}' => $discussionUrl,
        ]);

        $this->notifier->send($message);
    }

    private function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}