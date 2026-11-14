import Alpine from 'alpinejs';
import collapse from '@alpinejs/collapse';
import { Passkeys } from '@laravel/passkeys';

Alpine.plugin(collapse);

// Fehlertexte kommen übersetzt aus Blade (data-Attribute), nie hart im JS.
const messageFor = (error, messages) => {
    switch (error?.name) {
        case 'UserCancelledError':
            return null;
        case 'NotSupportedError':
            return messages.unsupported;
        case 'PasskeyExistsError':
            return messages.exists;
        default:
            return messages.failed;
    }
};

// Login mit Passkey (Standard Abschnitt 11, Rang 1)
Alpine.data('passkeyLogin', (messages = {}) => ({
    supported: Passkeys.isSupported(),
    busy: false,
    error: null,

    async init() {
        // Autofill: Browser bietet Passkeys direkt im E-Mail-Feld an (autocomplete="email webauthn")
        if (this.supported && (await Passkeys.isAutofillSupported())) {
            Passkeys.autofill({ remember: true })
                .then((response) => response && this.done(response))
                .catch(() => {});
        }
    },

    async login() {
        this.busy = true;
        this.error = null;
        try {
            this.done(await Passkeys.verify({ remember: true }));
        } catch (error) {
            this.error = messageFor(error, messages);
        } finally {
            this.busy = false;
        }
    },

    done(response) {
        window.location.href = response?.redirect || '/books';
    },
}));

// Passkey hinzufügen (Einstellungen → Anmeldung & Sicherheit)
Alpine.data('passkeyRegister', (messages = {}) => ({
    supported: Passkeys.isSupported(),
    name: '',
    busy: false,
    error: null,

    async register() {
        this.busy = true;
        this.error = null;
        try {
            await Passkeys.register({ name: this.name.trim() || messages.defaultName });
            window.location.reload();
        } catch (error) {
            this.error = messageFor(error, messages);
        } finally {
            this.busy = false;
        }
    },
}));

window.Alpine = Alpine;
Alpine.start();
