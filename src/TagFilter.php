<?php

namespace Stezkoy\FlarumTelegramNotify;

use Flarum\Discussion\Discussion;
use Flarum\Settings\SettingsRepositoryInterface;
use Illuminate\Support\Arr;

class TagFilter
{
    public function __construct(
        private readonly SettingsRepositoryInterface $settings,
    ) {}

    public function shouldNotify(Discussion $discussion): bool
    {
        $enabledTagIds = json_decode(
            (string) $this->settings->get('stezkoy-telegram-notify.enabled_tags', '[]'),
            true
        );

        if (!is_array($enabledTagIds) || $enabledTagIds === []) {
            return true;
        }

        if (!class_exists('Flarum\Tags\Tag')) {
            return true;
        }

        $tagIds = Arr::pluck($discussion->tags, 'id');

        return array_intersect($enabledTagIds, $tagIds) !== [];
    }
}
