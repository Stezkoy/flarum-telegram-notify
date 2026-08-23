import app from 'flarum/admin/app';
import TelegramNotifyAdminPage from './components/TelegramNotifyAdminPage';

app.initializers.add('stezkoy-telegram-notify', () => {
  app.extensionData.for('stezkoy-telegram-notify').registerPage(TelegramNotifyAdminPage);
});
