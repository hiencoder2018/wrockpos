// i18n.js

import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

// Import translation files
import enTranslation from './translations/en.json'; // English translation
import frTranslation from './translations/fr.json'; // French translation

const resources = {
  en: { translation: enTranslation },
  fr: { translation: frTranslation },
};

i18n.use(initReactI18next).init({
  resources,
  lng: 'en', // Default language
  interpolation: {
    escapeValue: false,
  },
});

export default i18n;
