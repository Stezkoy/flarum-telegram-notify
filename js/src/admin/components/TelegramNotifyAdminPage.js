import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Switch from 'flarum/common/components/Switch';

const PREFIX = 'stezkoy-telegram-notify';

const DEFAULT_DISCUSSION_TEMPLATE = '🆕 <b>{title}</b>\n👤 {author}\n{excerpt}\n👉 {url}';
const DEFAULT_POST_TEMPLATE = '💬 <b>{title}</b>\n👤 {author}\n{excerpt}\n👉 {url}';

const PLACEHOLDERS = [
  ['{title}', 'ph_title'],
  ['{author}', 'ph_author'],
  ['{excerpt}', 'ph_excerpt'],
  ['{url}', 'ph_url'],
  ['{tags}', 'ph_tags'],
];

const EXAMPLES = [
  ['ex_minimal', '{url}'],
  ['ex_compact', '🆕 <b>{title}</b>\n👤 {author}\n👉 {url}'],
  ['ex_full', '🆕 <a href="{url}"><b>{title}</b></a>\n🏷️ {tags}\n👤 {author}\n\n<i>{excerpt}</i>'],
];

export default class TelegramNotifyAdminPage extends ExtensionPage {
  oninit(vnode) {
    super.oninit(vnode);

    this.setting(PREFIX + '.use_topic_id', '');
  }

  content(vnode) {
    return m(
      '.ExtensionPage-settings',
      m(
        '.container',
        m('.TelegramNotifyAdmin', [this._connectionSection(), this._templatesSection()])
      )
    );
  }

  _connectionSection() {
    return m('section.TelegramNotifyAdmin-section', [
      m('h2', app.translator.trans(PREFIX + '.admin.connection_heading')),
      m('p.helpText', app.translator.trans(PREFIX + '.admin.connection_intro')),

      this.buildSettingComponent({
        type: 'password',
        setting: PREFIX + '.bot_token',
        label: app.translator.trans(PREFIX + '.admin.bot_token_label'),
        help: app.translator.trans(PREFIX + '.admin.bot_token_help'),
      }),

      this.buildSettingComponent({
        type: 'text',
        setting: PREFIX + '.chat_id',
        label: app.translator.trans(PREFIX + '.admin.chat_id_label'),
        help: app.translator.trans(PREFIX + '.admin.chat_id_help'),
      }),

      m('.Form-group', [
        m(
          Switch,
          {
            state: this._useTopic(),
            onchange: this._toggleTopic.bind(this),
          },
          app.translator.trans(PREFIX + '.admin.use_topic_switch')
        ),
        m('p.helpText', app.translator.trans(PREFIX + '.admin.use_topic_help')),
      ]),

      this._useTopic()
        ? this.buildSettingComponent({
            type: 'number',
            setting: PREFIX + '.topic_id',
            label: app.translator.trans(PREFIX + '.admin.topic_id_label'),
            help: app.translator.trans(PREFIX + '.admin.topic_id_help'),
          })
        : null,

      m('.Form-group.Form-controls', this.submitButton()),
    ]);
  }

  _templatesSection() {
    return m('section.TelegramNotifyAdmin-section', [
      m('h2', app.translator.trans(PREFIX + '.admin.templates_heading')),
      m('p.helpText', app.translator.trans(PREFIX + '.admin.templates_intro')),

      this.buildSettingComponent({
        type: 'textarea',
        setting: PREFIX + '.new_discussion_template',
        label: app.translator.trans(PREFIX + '.admin.new_discussion_label'),
        rows: 7,
        placeholder: DEFAULT_DISCUSSION_TEMPLATE,
      }),

      this.buildSettingComponent({
        type: 'textarea',
        setting: PREFIX + '.new_post_template',
        label: app.translator.trans(PREFIX + '.admin.new_post_label'),
        rows: 7,
        placeholder: DEFAULT_POST_TEMPLATE,
      }),

      this._hintsBox(),

      m('.Form-group.Form-controls', this.submitButton()),
    ]);
  }

  _hintsBox() {
    return m('.TelegramNotifyAdmin-hints', [
      m('h4', app.translator.trans(PREFIX + '.admin.placeholders_heading')),
      m(
        'ul',
        PLACEHOLDERS.map(([code, key]) =>
          m('li', [
            m('code', code),
            ' — ',
            app.translator.trans(PREFIX + '.admin.' + key),
          ])
        )
      ),
      m('p', [
        app.translator.trans(PREFIX + '.admin.html_hint') + ' ',
        m('code', '<b> <i> <u> <s> <a href=""> <code> <pre> <blockquote> <tg-spoiler>'),
      ]),
      m('h4', app.translator.trans(PREFIX + '.admin.examples_heading')),
      m(
        'ul',
        EXAMPLES.map(([key, code]) =>
          m('li.TelegramNotifyAdmin-example', [
            m('pre', code),
            ' — ',
            app.translator.trans(PREFIX + '.admin.' + key),
          ])
        )
      ),
    ]);
  }

  _useTopic() {
    return this.setting(PREFIX + '.use_topic_id', '')() === '1';
  }

  _toggleTopic(value) {
    this.setting(PREFIX + '.use_topic_id')(value ? '1' : '');

    m.redraw();
  }
}
