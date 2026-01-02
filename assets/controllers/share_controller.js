import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String
    }

    copy(event) {
        event.preventDefault();
        
        const button = event.currentTarget;
        const originalHTML = button.innerHTML;
        
        navigator.clipboard.writeText(this.urlValue).then(() => {
            button.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            `;
            
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 2000);
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }
}
