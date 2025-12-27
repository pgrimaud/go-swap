import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['grid', 'loader', 'searchInput', 'skeleton', 'spinner', 'loaderText'];
    
    static values = {
        url: String,
        page: { type: Number, default: 1 },
        loading: { type: Boolean, default: false },
        hasMore: { type: Boolean, default: true },
        variant: { type: String, default: '' },
        search: { type: String, default: '' }
    };

    connect() {
        console.log('Pokédex controller connected');
        this.observer = null;
        this.debounceTimer = null;
        
        // Read URL params on load
        this.readURLParams();
        
        // Set active filter button based on current variant
        this.setInitialActiveButton();
        
        // Initial load
        this.loadPokemon(false);
        
        // Setup infinite scroll
        this.setupIntersectionObserver();
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }
    }

    readURLParams() {
        const params = new URLSearchParams(window.location.search);
        this.variantValue = params.get('variant') || '';
        this.searchValue = params.get('search') || '';
        
        // Update search input if present
        if (this.hasSearchInputTarget && this.searchValue) {
            this.searchInputTarget.value = this.searchValue;
        }
    }

    async loadPokemon(append = false) {
        if (this.loadingValue || (!append && !this.hasMoreValue)) return;

        this.loadingValue = true;
        this.showLoading();

        const params = new URLSearchParams({
            variant: this.variantValue,
            search: this.searchValue,
            page: this.pageValue
        });

        try {
            const response = await fetch(`${this.urlValue}?${params}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();

            // Update hasMore BEFORE appending
            this.hasMoreValue = data.hasMore;

            if (append) {
                this.appendPokemon(data.pokemon);
            } else {
                this.replacePokemon(data.pokemon);
            }

            this.updateStatsBar(data);

        } catch (error) {
            console.error('Error loading Pokémon:', error);
            this.showError();
        } finally {
            this.loadingValue = false;
            this.hideLoading();
        }
    }

    filter(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const variant = button.dataset.variant || '';

        // Update active state on buttons IMMEDIATELY (synchronous)
        this.updateActiveFilterButton(button);
        
        // Force immediate repaint by reading a property that requires layout
        button.offsetHeight;

        this.variantValue = variant;
        this.pageValue = 1;
        this.hasMoreValue = true;

        this.updateURL();
        this.loadPokemon(false);
    }

    search(event) {
        clearTimeout(this.debounceTimer);
        
        this.debounceTimer = setTimeout(() => {
            this.searchValue = event.target.value.trim();
            this.pageValue = 1;
            this.hasMoreValue = true;

            this.updateURL();
            this.loadPokemon(false);
        }, 300);
    }

    replacePokemon(pokemon) {
        if (pokemon.length === 0) {
            this.gridTarget.innerHTML = this.emptyStateHTML();
            this.hideLoader();
            return;
        }

        this.gridTarget.innerHTML = '';
        pokemon.forEach(p => {
            this.gridTarget.insertAdjacentHTML('beforeend', this.createPokemonCard(p));
        });
        
        // Show loader if there are more pages
        this.showLoader();
    }

    appendPokemon(pokemon) {
        pokemon.forEach(p => {
            this.gridTarget.insertAdjacentHTML('beforeend', this.createPokemonCard(p));
        });
        
        // Show end message and hide loader if no more pages
        if (!this.hasMoreValue) {
            this.gridTarget.insertAdjacentHTML('beforeend', this.endOfListHTML());
            this.hideLoader();
        }
    }

    createPokemonCard(pokemon) {
        const number = String(pokemon.number).padStart(4, '0');
        const completed = false; // TODO: Real completion check when UserPokemon entity is ready

        return `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg cursor-pointer overflow-hidden">
                <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-750 px-3 py-2 flex justify-between items-center">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400">#${number}</span>
                    <span class="text-lg" title="${completed ? 'Completed' : 'Not completed'}">
                        ${completed ? '✅' : '⬜'}
                    </span>
                </div>
                <div class="p-4 flex justify-center">
                    <img src="/images/pokemon/normal/${pokemon.picture}" 
                         alt="${pokemon.name}"
                         loading="lazy"
                         class="w-24 h-24 object-contain">
                </div>
                <div class="px-3 pb-2 text-center">
                    <h3 class="font-semibold text-gray-800 dark:text-white text-sm mb-2">
                        ${this.escapeHtml(pokemon.name)}
                    </h3>
                </div>
                <div class="px-3 pb-3">
                    <div class="grid grid-cols-4 gap-1.5">
                        ${this.createVariantBadges()}
                    </div>
                </div>
            </div>
        `;
    }

    createTypesBadges(types) {
        if (!types || types.length === 0) return '';
        
        return `
            <div class="flex justify-center gap-1 flex-wrap">
                ${types.map(type => `
                    <span class="px-2 py-0.5 text-xs rounded bg-${type.color}-100 text-${type.color}-700 dark:bg-${type.color}-900 dark:text-${type.color}-200">
                        ${this.escapeHtml(type.name)}
                    </span>
                `).join('')}
            </div>
        `;
    }

    createVariantBadges() {
        const variants = [
            { slug: 'normal', icon: 'normal.png' },
            { slug: 'shiny', icon: 'shiny.png' },
            { slug: 'shadow', icon: 'shadow.png' },
            { slug: 'purified', icon: 'purified.png' },
            { slug: 'lucky', icon: 'lucky.png' },
            { slug: 'xxl', icon: 'xxl.png' },
            { slug: 'xxs', icon: 'xxs.png' },
            { slug: 'perfect', icon: 'perfect.png' }
        ];
        
        return variants.map(variant => `
            <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600 transition" title="${variant.slug}">
                <img src="/images/pokedex/${variant.icon}" alt="${variant.slug}" class="w-5 h-5 opacity-40">
            </div>
        `).join('');
    }

    emptyStateHTML() {
        return `
            <div class="col-span-full text-center py-12">
                <div class="text-6xl mb-4">🔍</div>
                <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">No Pokémon found</h3>
                <p class="text-gray-500 dark:text-gray-400">Try adjusting your filters or search term</p>
            </div>
        `;
    }

    endOfListHTML() {
        return `
            <div class="col-span-full text-center py-8">
                <p class="text-gray-500 dark:text-gray-400">✅ You've seen all Pokémon!</p>
            </div>
        `;
    }

    setupIntersectionObserver() {
        if (!this.hasLoaderTarget) return;

        this.observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && this.hasMoreValue && !this.loadingValue) {
                this.pageValue++;
                this.loadPokemon(true);
            }
        }, {
            root: null,
            rootMargin: '200px',
            threshold: 0.1
        });

        this.observer.observe(this.loaderTarget);
    }

    updateURL() {
        const params = new URLSearchParams();
        if (this.variantValue) params.set('variant', this.variantValue);
        if (this.searchValue) params.set('search', this.searchValue);

        const url = params.toString() ? `?${params}` : '';
        window.history.pushState({}, '', `/pokedex${url}`);
    }

    updateStatsBar(data) {
        // TODO: Update stats bar when UserPokemon entity is ready
        console.log('Stats:', data.total, 'Pokémon');
    }

    setInitialActiveButton() {
        // Find the button matching current variant
        const filterButtons = document.querySelectorAll('[data-action*="pokedex#filter"]');
        filterButtons.forEach(button => {
            const buttonVariant = button.dataset.variant || '';
            if (buttonVariant === this.variantValue) {
                this.updateActiveFilterButton(button);
            }
        });
    }

    updateActiveFilterButton(activeButton) {
        // Remove active class from all filter buttons
        const filterButtons = document.querySelectorAll('[data-action*="pokedex#filter"]');
        filterButtons.forEach(button => {
            button.classList.remove('bg-indigo-600', 'text-white', 'shadow-md');
            button.classList.add('opacity-60', 'bg-gray-100', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300', 'hover:bg-gray-200', 'dark:hover:bg-gray-600');
        });
        
        // Add active class to clicked button (no hover on active)
        activeButton.classList.remove('opacity-60', 'bg-gray-100', 'dark:bg-gray-700', 'text-gray-700', 'dark:text-gray-300', 'hover:bg-gray-200', 'dark:hover:bg-gray-600');
        activeButton.classList.add('bg-indigo-600', 'text-white', 'shadow-md');
    }

    showLoading() {
        if (this.hasSpinnerTarget) {
            this.spinnerTarget.classList.remove('hidden');
        }
        if (this.hasLoaderTextTarget) {
            this.loaderTextTarget.classList.remove('hidden');
        }
    }

    hideLoading() {
        if (this.hasSpinnerTarget) {
            this.spinnerTarget.classList.add('hidden');
        }
        if (this.hasLoaderTextTarget) {
            this.loaderTextTarget.classList.add('hidden');
        }
    }

    showLoader() {
        if (this.hasLoaderTarget) {
            this.loaderTarget.classList.remove('hidden');
        }
    }

    hideLoader() {
        if (this.hasLoaderTarget) {
            this.loaderTarget.classList.add('hidden');
        }
    }

    showError() {
        this.gridTarget.innerHTML = `
            <div class="col-span-full text-center py-12">
                <div class="text-6xl mb-4">❌</div>
                <h3 class="text-xl font-semibold text-red-600 mb-2">Error loading Pokémon</h3>
                <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    Retry
                </button>
            </div>
        `;
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}
