import { createI18n } from 'vue-i18n';
import en from './locales/en';
import pt from './locales/pt';

const messages = {
  en,
  pt
};

const i18n = createI18n({
  locale: localStorage.getItem('language') || 'pt',
  fallbackLocale: 'pt',
  messages
});

export default i18n;