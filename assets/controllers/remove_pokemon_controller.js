import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        customListPokemonId: Number
    }

    async remove(event) {
        event.preventDefault();
        
        if (!confirm('Remove this Pokémon from the list?')) {
            return;
        }

        try {
            const response = await fetch(this.urlValue, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (response.ok) {
                // Remove the card from DOM
                this.element.remove();
                
                // Show success message (optional)
                console.log(data.message);
            } else {
                alert(data.error || 'Failed to remove pokemon');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred while removing the pokemon');
        }
    }
}
