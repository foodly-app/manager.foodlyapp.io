import { createI18n } from 'vue-i18n';
import en from './locales/en.json';
import ka from './locales/ka.json';

const messages = {
    en,
    ka
};

// Get saved language from localStorage or default to Georgian
const savedLocale = localStorage.getItem('locale') || 'ka';

const i18n = createI18n({
    legacy: false, // Use Composition API mode
    locale: savedLocale,
    fallbackLocale: 'en',
    messages,
    globalInjection: true,
});

export default i18n;
