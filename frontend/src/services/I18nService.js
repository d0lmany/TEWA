export class I18nService
{
    constructor(language = this.getLanguage()) {
        this.language = language;
        this.dictionary = null;
        this.initialized = this.loadDictionary();
        console.log(`${this.language}-cluster is selected`)
    }

    getLanguage() {
        return navigator.language.split('-')[0] || 'en';
    }

    async loadDictionary() {
        try {
            const response = await fetch('/assets/dictionary.json');

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const dictionary = await response.json();
            this.dictionary = await dictionary;

        } catch (e) {
            console.error(e);
            this.dictionary = {};
        }
    }

    translate(key, lang = this.language) {
        if (!this.dictionary) {
            this.initialized;
        }

        const string = key.trim().toLowerCase();

        if (!this.dictionary[string]) {
            console.warn(`Key "${string}" is not found`);
            return string;
        }

        if (!this.dictionary[string][lang]) {
            console.warn(`Key "${string}" is not included in ${lang}-cluster`);
            return string;
        }

        return this.dictionary[string][lang];
    }
}