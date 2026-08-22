<?php

namespace Stezkoy\NouiTeleNotify;

use Flarum\Http\UrlGenerator;
use Flarum\Locale\Translator;
use Flarum\Post\Event\Posted;

class NewPostListener
{
    public function __construct(
        private readonly TelegramNotifier $notifier,
        private readonly UrlGenerator $url,
        private readonly Translator $translator,
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

        $message = $this->translator->trans('stezkoy-noui-tele-notify.forum.new_post', [
            '{title}' => $this->escape($title),
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