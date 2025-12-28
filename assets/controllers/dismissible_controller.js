import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        key: String
    }

    connect() {
        const isDismissed = this.getCookie(this.keyValue);
        if (isDismissed === 'true') {
            this.element.remove();
        }
    }

    dismiss() {
        this.setCookie(this.keyValue, 'true', 365);
        this.element.classList.add('transition-opacity', 'duration-300', 'opacity-0');
        setTimeout(() => {
            this.element.remove();
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
}
