import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['button'];
    static values = {
        numbers: String
    };

    copy() {
        // Copy numbers to clipboard
        navigator.clipboard.writeText(this.numbersValue).then(() => {
            // Visual feedback
            if (this.hasButtonTarget) {
                const originalText = this.buttonTarget.innerHTML;
                this.buttonTarget.innerHTML = '✓';
                setTimeout(() => {
                    this.buttonTarget.innerHTML = originalText;
                }, 1000);
            }
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
}
