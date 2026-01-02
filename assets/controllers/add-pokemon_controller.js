import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['searchInput', 'resultsContainer', 'pokemonGrid'];
    
    static values = {
        searchUrl: String,
        addUrl: String,
        listId: Number
    };

    connect() {
        this.debounceTimer = null;
        this.currentPokemon = [];
    }

    disconnect() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
    }

    search() {
        clearTimeout(this.debounceTimer);
        
        this.debounceTimer = setTimeout(() => {
            this.performSearch();
        }, 300);
    }

    async performSearch() {
        const query = this.searchInputTarget.value.trim();
        
        if (query.length < 1) {
            this.hideResults();
            return;
        }

        try {
            const url = new URL(this.searchUrlValue, window.location.origin);
            url.searchParams.set('search', query);
            url.searchParams.set('limit', '10');

            const response = await fetch(url);
            const data = await response.json();

            if (response.ok && data.pokemon) {
                this.displayResults(data.pokemon);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    displayResults(pokemon) {
        if (!pokemon.length) {
            this.resultsContainerTarget.innerHTML = '<div class="p-4 text-gray-500 dark:text-gray-400">No Pokémon found</div>';
            this.resultsContainerTarget.classList.remove('hidden');
            return;
        }

        this.currentPokemon = pokemon;

        const html = pokemon.map(p => `
            <div class="flex items-center gap-3 p-3 hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
                <img 
                    src="/images/pokemon/normal/${p.slug}.png" 
                    alt="${p.name}"
                    class="w-12 h-12 object-contain"
                    onerror="this.src='/images/pokemon/normal/0.png'"
                >
                <div class="flex-1">
                    <div class="font-medium text-gray-900 dark:text-white">${p.name}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">#${String(p.number).padStart(4, '0')}</div>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        data-pokemon-id="${p.id}"
                        data-is-shiny="false"
                        data-action="click->add-pokemon#add"
                        class="px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-600 dark:hover:border-violet-600 text-gray-700 dark:text-gray-300 hover:text-violet-700 dark:hover:text-violet-400 rounded font-medium text-sm transition cursor-pointer"
                        title="Add Normal"
                    >
                        Normal
                    </button>
                    ${p.availableVariants?.shiny ? `
                    <button
                        type="button"
                        data-pokemon-id="${p.id}"
                        data-is-shiny="true"
                        data-action="click->add-pokemon#add"
                        class="px-3 py-1.5 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-600 dark:hover:border-violet-600 text-gray-700 dark:text-gray-300 hover:text-violet-700 dark:hover:text-violet-400 rounded font-medium text-sm transition cursor-pointer inline-flex items-center gap-1"
                        title="Add Shiny"
                    >
                        <span>✨</span> Shiny
                    </button>
                    ` : ''}
                </div>
            </div>
        `).join('');

        this.resultsContainerTarget.innerHTML = html;
        this.resultsContainerTarget.classList.remove('hidden');
    }

    hideResults() {
        this.resultsContainerTarget.classList.add('hidden');
        this.resultsContainerTarget.innerHTML = '';
    }

    async add(event) {
        event.preventDefault();
        
        const button = event.currentTarget;
        const pokemonId = parseInt(button.dataset.pokemonId);
        const isShiny = button.dataset.isShiny === 'true';
        const pokemon = this.currentPokemon.find(p => p.id === pokemonId);

        if (!pokemon) return;

        // Disable button during request
        button.disabled = true;
        button.classList.add('opacity-50');

        try {
            const url = this.addUrlValue
                .replace('LIST_ID', this.listIdValue)
                .replace('POKEMON_ID', pokemonId);

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ isShiny })
            });

            const data = await response.json();

            if (response.ok) {
                // Add Pokemon card to grid
                this.addPokemonCard(data.data);
                
                // Update counter
                const counter = document.getElementById('pokemon-count');
                if (counter) {
                    counter.textContent = parseInt(counter.textContent) + 1;
                }

                // Clear search
                this.searchInputTarget.value = '';
                this.hideResults();

                // Remove empty state if present
                const emptyState = this.pokemonGridTarget.querySelector('.text-center.py-8');
                if (emptyState) {
                    emptyState.remove();
                }
            } else {
                // Silent fail for conflict (Pokemon already in list)
                if (response.status === 409) {
                    this.searchInputTarget.value = '';
                    this.hideResults();
                } else {
                    console.error('Failed to add pokemon:', data.error);
                }
            }
        } catch (error) {
            console.error('Error:', error);
        } finally {
            button.disabled = false;
            button.classList.remove('opacity-50');
        }
    }

    addPokemonCard(data) {
        const { id, pokemon, isShiny } = data;
        const variant = isShiny ? 'shiny' : 'normal';
        
        // Check if grid exists or create it
        let grid = this.pokemonGridTarget.querySelector('.grid.grid-cols-3');
        
        if (!grid) {
            // Create grid if it doesn't exist (empty state)
            this.pokemonGridTarget.innerHTML = `
                <div class="grid grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                </div>
            `;
            grid = this.pokemonGridTarget.querySelector('.grid.grid-cols-3');
        }

        const card = document.createElement('div');
        card.className = 'relative group';
        card.dataset.controller = 'remove-pokemon';
        card.dataset.removePokemonUrlValue = `/api/custom-lists/pokemon/${id}`;
        card.innerHTML = `
            <div class="bg-gray-50 dark:bg-zinc-900 rounded-lg p-3 hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
                <img 
                    src="/images/pokemon/${variant}/${pokemon.slug}.png" 
                    alt="${pokemon.name}"
                    class="w-full h-auto mb-1"
                    onerror="this.src='/images/pokemon/normal/0.png'"
                >
                <p class="text-xs text-center font-medium text-gray-900 dark:text-white truncate">
                    ${isShiny ? '✨ ' : ''}${pokemon.name}
                </p>
            </div>
            <button 
                data-action="click->remove-pokemon#remove"
                class="absolute top-1 right-1 w-5 h-5 bg-red-600 hover:bg-red-700 text-white rounded-full md:opacity-0 md:group-hover:opacity-100 transition flex items-center justify-center cursor-pointer z-20"
                title="Remove from list"
            >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;

        grid.insertBefore(card, grid.firstChild);
    }

    // Click outside to close results
    handleOutsideClick(event) {
        if (!this.element.contains(event.target)) {
            this.hideResults();
        }
    }
}
