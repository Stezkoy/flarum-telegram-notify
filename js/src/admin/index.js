import app from 'flarum/admin/app';
import TelegramNotifyAdminPage from './components/TelegramNotifyAdminPage';

app.initializers.add('stezkoy-flarum-telegram-notify', () => {
  app.registry.for('stezkoy-flarum-telegram-notify').registerPage(TelegramNotifyAdminPage);
});
