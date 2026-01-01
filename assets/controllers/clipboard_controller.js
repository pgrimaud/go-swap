import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        text: String
    }

    copy() {
        navigator.clipboard.writeText(this.textValue).then(() => {
            // Show success feedback
            const originalHTML = this.element.innerHTML;
            this.element.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            `;
            this.element.classList.add('border-green-500', 'dark:border-green-500');
            
            setTimeout(() => {
                this.element.innerHTML = originalHTML;
                this.element.classList.remove('border-green-500', 'dark:border-green-500');
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
}
