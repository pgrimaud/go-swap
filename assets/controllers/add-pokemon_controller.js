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
            <div class="p-3 hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
                <div class="flex items-center gap-3 mb-2">
                    <img 
                        src="/images/pokemon/normal/${p.slug}.png" 
                        alt="${p.name}"
                        class="w-12 h-12 object-contain flex-shrink-0"
                        onerror="this.src='/images/pokemon/normal/0.png'"
                    >
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 dark:text-white truncate">${p.name}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">#${String(p.number).padStart(4, '0')}</div>
                    </div>
                    <div class="hidden sm:flex gap-2 flex-shrink-0">
                        <button
                            type="button"
                            data-pokemon-id="${p.id}"
                            data-is-shiny="false"
                            data-action="click->add-pokemon#add"
                            class="px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-600 dark:hover:border-violet-600 text-gray-700 dark:text-gray-300 hover:text-violet-700 dark:hover:text-violet-400 rounded font-medium text-sm transition cursor-pointer"
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
                            class="px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-600 dark:hover:border-violet-600 text-gray-700 dark:text-gray-300 hover:text-violet-700 dark:hover:text-violet-400 rounded font-medium text-sm transition cursor-pointer inline-flex items-center justify-center gap-1"
                            title="Add Shiny"
                        >
                            <span>✨</span> Shiny
                        </button>
                        ` : ''}
                    </div>
                </div>
                <div class="flex sm:hidden gap-2">
                    <button
                        type="button"
                        data-pokemon-id="${p.id}"
                        data-is-shiny="false"
                        data-action="click->add-pokemon#add"
                        class="flex-1 px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-600 dark:hover:border-violet-600 text-gray-700 dark:text-gray-300 hover:text-violet-700 dark:hover:text-violet-400 rounded font-medium text-sm transition cursor-pointer"
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
                        class="flex-1 px-3 py-2 bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-600 hover:bg-violet-50 dark:hover:bg-violet-900/20 hover:border-violet-600 dark:hover:border-violet-600 text-gray-700 dark:text-gray-300 hover:text-violet-700 dark:hover:text-violet-400 rounded font-medium text-sm transition cursor-pointer inline-flex items-center justify-center gap-1"
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
        let grid = this.pokemonGridTarget.querySelector('.grid.grid-cols-2');
        
        if (!grid) {
            // Create grid if it doesn't exist (empty state)
            this.pokemonGridTarget.innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
                </div>
            `;
            grid = this.pokemonGridTarget.querySelector('.grid.grid-cols-2');
        }

        const card = document.createElement('div');
        card.className = 'relative group';
        card.dataset.controller = 'remove-pokemon';
        card.dataset.removePokemonUrlValue = `/api/custom-lists/pokemon/${id}`;
        card.innerHTML = `
            <div 
                class="bg-gray-50 dark:bg-zinc-900 rounded-lg p-3 hover:bg-red-50 dark:hover:bg-red-950/30 hover:border-red-500 dark:hover:border-red-500 border-2 border-transparent transition cursor-pointer relative"
                data-action="click->remove-pokemon#remove"
                title="Click to remove ${pokemon.name} from list"
            >
                ${isShiny ? '<span class="absolute top-3 left-3 text-[14px] leading-none pointer-events-none" title="Shiny">✨</span>' : ''}
                <!-- Delete icon (always visible on mobile, on hover on desktop) -->
                <div class="absolute top-2 right-2 bg-red-600 text-white rounded-full p-1.5 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity pointer-events-none shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <img 
                    src="/images/pokemon/${variant}/${pokemon.slug}.png" 
                    alt="${pokemon.name}"
                    class="w-full h-auto mb-1 pointer-events-none"
                    onerror="this.src='/images/pokemon/normal/0.png'"
                >
                <p class="text-xs text-center font-medium text-gray-900 dark:text-white truncate pointer-events-none">
                    ${pokemon.name}
                </p>
            </div>
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
