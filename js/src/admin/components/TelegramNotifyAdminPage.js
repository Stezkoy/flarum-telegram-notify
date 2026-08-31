import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import Switch from 'flarum/common/components/Switch';
import extractText from 'flarum/common/utils/extractText';

const PREFIX = 'stezkoy-telegram-notify';

const VOID_TAGS = new Set([
  'area',
  'base',
  'br',
  'col',
  'embed',
  'hr',
  'img',
  'input',
  'link',
  'meta',
  'param',
  'source',
  'track',
  'wbr',
]);

const DEFAULT_DISCUSSION_TEMPLATE = '🆕 <b>{title}</b>\n👤 {author}\n{excerpt}\n👉 {url}';
const DEFAULT_POST_TEMPLATE = '💬 <b>{title}</b>\n👤 {author}\n{excerpt}\n👉 {url}';

const PLACEHOLDERS = [
  ['{title}', 'ph_title'],
  ['{author}', 'ph_author'],
  ['{excerpt}', 'ph_excerpt'],
  ['{url}', 'ph_url'],
  ['{tags}', 'ph_tags'],
];

const HTML_TAGS = [
  ['<b></b>', 'tag_bold'],
  ['<i></i>', 'tag_italic'],
  ['<u></u>', 'tag_underline'],
  ['<s></s>', 'tag_strike'],
  ['<a href=""></a>', 'tag_link'],
  ['<code></code>', 'tag_code'],
  ['<pre></pre>', 'tag_pre'],
  ['<blockquote></blockquote>', 'tag_quote'],
  ['<tg-spoiler></tg-spoiler>', 'tag_spoiler'],
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
    this.testing = false;
  }

  onsubmit(e) {
    const error = this._validateTemplates();

    if (error) {
      e.preventDefault();
      app.alerts.show({ type: 'error' }, error);

      return;
    }

    super.onsubmit(e);
  }

  content(vnode) {
    return m(
      '.ExtensionPage-settings',
      m(
        '.container',
        m(
          'form.TelegramNotifyAdmin',
          [this._connectionSection(), this._templatesSection(), this._hintsSection()]
        )
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
        placeholder: '1234567890:AAF3cBd4Ee5Ff6Gg7Hh8Ii9Jj0Kk1Ll',
        label: app.translator.trans(PREFIX + '.admin.bot_token_label'),
        help: app.translator.trans(PREFIX + '.admin.bot_token_help'),
      }),

      this.buildSettingComponent({
        type: 'text',
        setting: PREFIX + '.chat_id',
        placeholder: '-1001234567890',
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
            placeholder: '123',
            label: app.translator.trans(PREFIX + '.admin.topic_id_label'),
            help: app.translator.trans(PREFIX + '.admin.topic_id_help'),
          })
        : null,

      m('.Form-group', [
        m(
          Switch,
          {
            state: this._useProxy(),
            onchange: this._toggleProxy.bind(this),
          },
          app.translator.trans(PREFIX + '.admin.use_proxy_switch')
        ),
        m('p.helpText', app.translator.trans(PREFIX + '.admin.use_proxy_help')),
      ]),

      this._useProxy()
        ? this.buildSettingComponent({
            type: 'text',
            setting: PREFIX + '.proxy',
            placeholder: 'socks5://127.0.0.1:1080',
            label: app.translator.trans(PREFIX + '.admin.proxy_label'),
            help: app.translator.trans(PREFIX + '.admin.proxy_help'),
          })
        : null,

      this._tagsGroup(),

      m('.Form-group.Form-controls', [
        m(
          Button,
          {
            className: 'Button',
            icon: 'fas fa-paper-plane',
            loading: this.testing,
            onclick: this._sendTest.bind(this),
          },
          app.translator.trans(PREFIX + '.admin.test_button')
        ),
      ]),

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
        rows: 7,
        placeholder: DEFAULT_DISCUSSION_TEMPLATE,
        label: app.translator.trans(PREFIX + '.admin.new_discussion_label'),
      }),

      this.buildSettingComponent({
        type: 'textarea',
        setting: PREFIX + '.new_post_template',
        rows: 7,
        placeholder: DEFAULT_POST_TEMPLATE,
        label: app.translator.trans(PREFIX + '.admin.new_post_label'),
      }),

      m('.Form-group.Form-controls', this.submitButton()),
    ]);
  }

  _hintsSection() {
    return m('section.TelegramNotifyAdmin-section', [
      m('h2', app.translator.trans(PREFIX + '.admin.hints_heading')),
      this._hintsBox(),
    ]);
  }

  _hintsBox() {
    return m(
      'details.TelegramNotifyAdmin-hints',
      [
        m('summary', app.translator.trans(PREFIX + '.admin.hints_summary')),
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
      m('h4', app.translator.trans(PREFIX + '.admin.html_hint')),
      m(
        'ul',
        HTML_TAGS.map(([tag, key]) =>
          m('li', [
            m('code', tag),
            ' — ',
            app.translator.trans(PREFIX + '.admin.' + key),
          ])
        )
      ),
      m('h4', app.translator.trans(PREFIX + '.admin.examples_heading')),
      m(
        'ul.TelegramNotifyAdmin-examples',
        [
          ...EXAMPLES,
          [
            'ex_button',
            `💬 <b>{title}</b>\n👤 {author}\n{excerpt}\n\n👉 <a href="{url}">${extractText(
              app.translator.trans(PREFIX + '.admin.ex_link_word')
            )}</a>`,
          ],
        ].map(([key, code]) =>
          m('li', [
            m('pre', code),
            m('.TelegramNotifyAdmin-exampleNote', app.translator.trans(PREFIX + '.admin.' + key)),
          ])
        )
      ),
      ]
    );
  }

  _useTopic() {
    return this.setting(PREFIX + '.use_topic_id', '')() === '1';
  }

  _sendTest() {
    if (this.testing) {
      return;
    }

    this.testing = true;

    app
      .request({
        method: 'POST',
        url: app.forum.attribute('apiUrl') + '/telegram-notify/test',
      })
      .then(
        (response) => {
          this.testing = false;

          if (response.success) {
            app.alerts.show({ type: 'success' }, app.translator.trans(PREFIX + '.admin.test_ok'));
          } else {
            app.alerts.show({ type: 'error' }, response.error || app.translator.trans(PREFIX + '.admin.test_failed'));
          }

          m.redraw();
        },
        () => {
          this.testing = false;
          app.alerts.show({ type: 'error' }, app.translator.trans(PREFIX + '.admin.test_failed'));
          m.redraw();
        }
      );
  }

  _toggleTopic(value) {
    this.setting(PREFIX + '.use_topic_id')(value ? '1' : '');

    m.redraw();
  }

  _useProxy() {
    return this.setting(PREFIX + '.use_proxy', '')() === '1';
  }

  _toggleProxy(value) {
    this.setting(PREFIX + '.use_proxy')(value ? '1' : '');

    m.redraw();
  }

  _tagsGroup() {
    const selectedIds = this._selectedTagIds();
    const allTags = app.store.all('tags');

    return m('.Form-group', [
      m('label', app.translator.trans(PREFIX + '.admin.enabled_tags_label')),
      allTags.length === 0
        ? m('p.helpText', app.translator.trans(PREFIX + '.admin.enabled_tags_empty'))
        : m('.TelegramNotifyAdminTagList', [
            allTags.map((tag) => {
              const id = String(tag.id());
              const checked = selectedIds.includes(id);

              return m(
                'button.TelegramNotifyAdminTag',
                {
                  type: 'button',
                  className: checked ? 'selected' : '',
                  onclick: () => this._toggleTag(id),
                },
                tag.name()
              );
            }),
          ]),
      m('p.helpText', app.translator.trans(PREFIX + '.admin.enabled_tags_help')),
    ]);
  }

  _toggleTag(id) {
    const selected = this._selectedTagIds();
    const index = selected.indexOf(id);

    if (index === -1) {
      selected.push(id);
    } else {
      selected.splice(index, 1);
    }

    this.setting(PREFIX + '.enabled-tags')(JSON.stringify(selected));
    m.redraw();
  }

  _selectedTagIds() {
    let selected = [];
    try {
      selected = JSON.parse(this.setting(PREFIX + '.enabled-tags', '[]')() || '[]');
    } catch (e) {
      selected = [];
    }

    return selected.map(String);
  }

  _validateTemplates() {
    const discussion = this.setting(PREFIX + '.new_discussion_template')();
    const post = this.setting(PREFIX + '.new_post_template')();

    const discussionError = this._validateHtml(discussion);
    if (discussionError) {
      return app.translator.trans(PREFIX + '.admin.template_invalid_discussion', { error: discussionError });
    }

    const postError = this._validateHtml(post);
    if (postError) {
      return app.translator.trans(PREFIX + '.admin.template_invalid_post', { error: postError });
    }

    return null;
  }

  _validateHtml(template) {
    const stack = [];
    const tagRegex = /<\/?([a-zA-Z][a-zA-Z0-9-]*)((?:"[^"]*"|'[^']*'|[^"'>])*)>/g;

    let match;
    while ((match = tagRegex.exec(template)) !== null) {
      const full = match[0];
      const name = match[1].toLowerCase();
      const attrs = match[2];

      if (full.startsWith('</')) {
        const open = stack.pop();
        if (open !== name) {
          return app.translator.trans(PREFIX + '.admin.template_mismatch', { open: open || '?', close: name });
        }
      } else if (!full.endsWith('/>') && !VOID_TAGS.has(name)) {
        stack.push(name);
      }
    }

    if (stack.length > 0) {
      return app.translator.trans(PREFIX + '.admin.template_unclosed', { tag: stack[stack.length - 1] });
    }

    return null;
  }
}
