import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        key: String
    }

    connect() {
        // Check if user just logged in (clear cookie if so)
        const justLoggedIn = sessionStorage.getItem('just_logged_in');
        if (justLoggedIn === 'true') {
            // Clear the dismissed cookie
            this.deleteCookie(this.keyValue);
            sessionStorage.removeItem('just_logged_in');
        }

        const isDismissed = this.getCookie(this.keyValue);
        if (isDismissed !== 'true') {
            // Show banner only if not dismissed
            this.element.classList.remove('hidden');
        }
    }

    dismiss() {
        this.setCookie(this.keyValue, 'true', 365);
        this.element.classList.add('transition-opacity', 'duration-300', 'opacity-0');
        setTimeout(() => {
            this.element.classList.add('hidden');
        }, 300);
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        if (parts.length === 2) return parts.pop().split(';').shift();
        return null;
    }

    setCookie(name, value, days) {
        const expires = new Date();
        expires.setTime(expires.getTime() + days * 24 * 60 * 60 * 1000);
        document.cookie = `${name}=${value};expires=${expires.toUTCString()};path=/;SameSite=Lax`;
    }

    deleteCookie(name) {
        document.cookie = `${name}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/;SameSite=Lax`;
    }
}
