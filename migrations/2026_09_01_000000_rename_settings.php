<?php

use Illuminate\Database\Schema\Builder;

/*
 * Data migration: renames (and moves) stored settings keys by touching the
 * settings table directly. Flarum\Database\Migration has no dedicated
 * settings-rename helper, and resolving SettingsRepositoryInterface here
 * would require the container at migration time. Direct table access is
 * intentional and matches how other Flarum extensions rename settings.
 */

return [
    'up' => function (Builder $schema) {
        $renames = [
            'stezkoy-telegram-notify.enabled-tags' => 'stezkoy-telegram-notify.enabled_tags',
            'stezkoy-telegram-notify.use_topic_id' => 'stezkoy-telegram-notify.use_topic',
        ];

        foreach ($renames as $old => $new) {
            $value = $schema->getConnection()
                ->table('settings')
                ->where('key', $old)
                ->value('value');

            if ($value !== null) {
                $schema->getConnection()
                    ->table('settings')
                    ->updateOrInsert(
                        ['key' => $new],
                        ['key' => $new, 'value' => $value]
                    );

                $schema->getConnection()
                    ->table('settings')
                    ->where('key', $old)
                    ->delete();
            }
        }
    },

    'down' => function (Builder $schema) {
        $renames = [
            'stezkoy-telegram-notify.enabled_tags' => 'stezkoy-telegram-notify.enabled-tags',
            'stezkoy-telegram-notify.use_topic' => 'stezkoy-telegram-notify.use_topic_id',
        ];

        foreach ($renames as $old => $new) {
            $value = $schema->getConnection()
                ->table('settings')
                ->where('key', $old)
                ->value('value');

            if ($value !== null) {
                $schema->getConnection()
                    ->table('settings')
                    ->updateOrInsert(
                        ['key' => $new],
                        ['key' => $new, 'value' => $value]
                    );

                $schema->getConnection()
                    ->table('settings')
                    ->where('key', $old)
                    ->delete();
            }
        }
    }
];