import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'caret'];

    connect() {
        this.isOpen = false;
    }

    toggle(event) {
        event.preventDefault();
        event.stopPropagation();
        this.isOpen = !this.isOpen;
        this.menuTarget.classList.toggle('hidden');
        
        if (this.hasCaretTarget) {
            this.caretTarget.classList.toggle('rotate-180');
        }
    }

    closeOnClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.isOpen = false;
            this.menuTarget.classList.add('hidden');
            
            if (this.hasCaretTarget) {
                this.caretTarget.classList.remove('rotate-180');
            }
        }
    }

    disconnect() {
        this.menuTarget.classList.add('hidden');
        if (this.hasCaretTarget) {
            this.caretTarget.classList.remove('rotate-180');
        }
    }
}
