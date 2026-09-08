import ExtensionPage from 'flarum/admin/components/ExtensionPage';
import Button from 'flarum/common/components/Button';
import Switch from 'flarum/common/components/Switch';
import extractText from 'flarum/common/utils/extractText';

const PREFIX = 'stezkoy-telegram-notify';

const DEFAULT_ATTR_DISCUSSION = PREFIX + '.default_new_discussion_template';
const DEFAULT_ATTR_POST = PREFIX + '.default_new_post_template';

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

    this.setting(PREFIX + '.use_topic', '');
    this.setting(PREFIX + '.use_proxy', '');
    this.testing = false;

    this.defaultDiscussionTemplate = app.forum.attribute(DEFAULT_ATTR_DISCUSSION);
    this.defaultPostTemplate = app.forum.attribute(DEFAULT_ATTR_POST);
  }

  saveSettings(e) {
    const error = this._validateTemplates();

    if (error) {
      e.preventDefault();
      app.alerts.show({ type: 'error' }, error);
      m.redraw();

      return;
    }

    super.saveSettings(e);
  }

  content(vnode) {
    return m('.ExtensionPage-settings', m('.container', [
      m('.TelegramNotifySettings', [
        this._connectionSection(),
        this._deliverySection(),
        this._tagsSection(),
        this._templatesSection(),
        this._hintsSection(),
        m('.Form-group.Form-controls', this.submitButton()),
      ]),
    ]));
  }

  _section(titleKey, children) {
    return m('.TelegramNotifySettings-section', [
      m('h3', app.translator.trans(PREFIX + '.' + titleKey)),
      m('.TelegramNotifySettings-sectionBody', children),
    ]);
  }

  _connectionSection() {
    return this._section('admin.connection_heading', [
      m('p.helpText', app.translator.trans(PREFIX + '.admin.connection_intro')),
      this._textField(PREFIX + '.bot_token', 'admin.bot_token_label', 'admin.bot_token_help', {
        type: 'password',
        placeholder: '1234567890:AAF3cBd4Ee5Ff6Gg7Hh8Ii9Jj0Kk1Ll',
      }),
      this._textField(PREFIX + '.chat_id', 'admin.chat_id_label', 'admin.chat_id_help', {
        placeholder: '-1001234567890',
      }),
      this._toggle(PREFIX + '.use_topic', 'admin.use_topic_switch', 'admin.use_topic_help'),
      this._flagOn(PREFIX + '.use_topic')
        ? this._textField(PREFIX + '.topic_id', 'admin.topic_id_label', 'admin.topic_id_help', {
            type: 'number',
            min: 1,
            placeholder: '123',
          })
        : null,
      this._toggle(PREFIX + '.use_proxy', 'admin.use_proxy_switch', 'admin.use_proxy_help'),
      this._flagOn(PREFIX + '.use_proxy')
        ? this._textField(PREFIX + '.proxy', 'admin.proxy_label', 'admin.proxy_help', {
            placeholder: 'socks5://127.0.0.1:1080',
          })
        : null,
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
    ]);
  }

  _deliverySection() {
    return this._section('admin.delivery_heading', [
      m('p.helpText', app.translator.trans(PREFIX + '.admin.delivery_intro')),
      this._textField(PREFIX + '.retry_attempts', 'admin.retry_attempts_label', 'admin.retry_attempts_help', {
        type: 'number',
        min: 1,
        max: 5,
        placeholder: '2',
      }),
      this._textField(PREFIX + '.retry_delay', 'admin.retry_delay_label', 'admin.retry_delay_help', {
        type: 'number',
        min: 0,
        max: 30,
        placeholder: '1',
      }),
      this._textField(PREFIX + '.connect_timeout', 'admin.connect_timeout_label', 'admin.connect_timeout_help', {
        type: 'number',
        min: 1,
        max: 30,
        placeholder: '5',
      }),
      this._textField(PREFIX + '.timeout', 'admin.timeout_label', 'admin.timeout_help', {
        type: 'number',
        min: 1,
        max: 60,
        placeholder: '10',
      }),
    ]);
  }

  _tagsSection() {
    if (!app.data.extensions['flarum-tags']) {
      return this._section('admin.tags_heading', [
        m('p.helpText', app.translator.trans(PREFIX + '.admin.enabled_tags_empty')),
      ]);
    }

    return this._section('admin.tags_heading', [
      this.buildSettingComponent({
        type: 'flarum-tags.select-tags',
        setting: PREFIX + '.enabled_tags',
        help: app.translator.trans(PREFIX + '.admin.enabled_tags_help'),
      }),
    ]);
  }

  _templatesSection() {
    return this._section('admin.templates_heading', [
      m('p.helpText', app.translator.trans(PREFIX + '.admin.templates_intro')),
      m('.Form-group', [
        m('label', app.translator.trans(PREFIX + '.admin.new_discussion_label')),
        m('textarea.FormControl', {
          rows: 7,
          bidi: this.setting(PREFIX + '.new_discussion_template'),
          placeholder: this.defaultDiscussionTemplate,
        }),
      ]),
      m('.Form-group', [
        m('label', app.translator.trans(PREFIX + '.admin.new_post_label')),
        m('textarea.FormControl', {
          rows: 7,
          bidi: this.setting(PREFIX + '.new_post_template'),
          placeholder: this.defaultPostTemplate,
        }),
      ]),
    ]);
  }

  _hintsSection() {
    return this._section('admin.hints_heading', [
      this._hintsBox(),
    ]);
  }

  _hintsBox() {
    return m(
      'details.TelegramNotifySettings-hints',
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
          'ul.TelegramNotifySettings-examples',
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
              m('.TelegramNotifySettings-exampleNote', app.translator.trans(PREFIX + '.admin.' + key)),
            ])
          )
        ),
      ]
    );
  }

  _textField(key, labelKey, helpKey, attrs = {}) {
    return m('.Form-group', [
      m('label', app.translator.trans(PREFIX + '.' + labelKey)),
      m('input.FormControl', { type: 'text', ...attrs, bidi: this.setting(key) }),
      m('p.helpText', app.translator.trans(PREFIX + '.' + helpKey)),
    ]);
  }

  _toggle(key, labelKey, descKey) {
    return m('.Form-group', [
      m(
        Switch,
        {
          state: this._flagOn(key),
          onchange: (value) => {
            this.setting(key)(value ? '1' : '');
            m.redraw();
          },
        },
        app.translator.trans(PREFIX + '.' + labelKey)
      ),
      m('p.helpText', app.translator.trans(PREFIX + '.' + descKey)),
    ]);
  }

  _flagOn(key) {
    const value = this.setting(key, '')();
    return value === '1' || value === true || value === 1;
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

  _validateTemplates() {
    const discussion = this.setting(PREFIX + '.new_discussion_template')();
    const post = this.setting(PREFIX + '.new_post_template')();

    const discussionError = this._validateHtml(discussion);
    if (discussionError) {
      return app.translator.trans(PREFIX + '.admin.template_invalid_discussion', { error: discussionError }, true);
    }

    const postError = this._validateHtml(post);
    if (postError) {
      return app.translator.trans(PREFIX + '.admin.template_invalid_post', { error: postError }, true);
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

      if (full.startsWith('</')) {
        const open = stack.pop();
        if (open !== name) {
          return app.translator.trans(
            PREFIX + '.admin.template_mismatch',
            {
              open: open ? '<' + open + '>' : '?',
              close: '<' + name + '>',
            },
            true
          );
        }
      } else if (!full.endsWith('/>') && !VOID_TAGS.has(name)) {
        stack.push(name);

        if (name === 'a' && !/\shref\s*=/i.test(match[2])) {
          return app.translator.trans(PREFIX + '.admin.template_missing_href', { tag: '<a>' }, true);
        }
      }
    }

    if (stack.length > 0) {
      return app.translator.trans(
        PREFIX + '.admin.template_unclosed',
        {
          tag: '<' + stack[stack.length - 1] + '>',
        },
        true
      );
    }

    return null;
  }
}
