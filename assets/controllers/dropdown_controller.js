import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu'];

    toggle(event) {
        event.stopPropagation();
        const isOpen = !this.menuTarget.classList.contains('header-dropdown--hidden');

        if (isOpen) {
            this.close();
        } else {
            this.menuTarget.classList.remove('header-dropdown--hidden');
            requestAnimationFrame(() => {
                this.menuTarget.classList.add('header-dropdown--visible');
            });
        }
    }

    close() {
        this.menuTarget.classList.remove('header-dropdown--visible');
        this.menuTarget.classList.add('header-dropdown--hidden');
    }

    closeOnClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    closeOnEscape(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }

    connect() {
        this._boundClose = this.closeOnClickOutside.bind(this);
        this._boundEscape = this.closeOnEscape.bind(this);
        document.addEventListener('click', this._boundClose);
        document.addEventListener('keydown', this._boundEscape);
    }

    disconnect() {
        document.removeEventListener('click', this._boundClose);
        document.removeEventListener('keydown', this._boundEscape);
    }
}
