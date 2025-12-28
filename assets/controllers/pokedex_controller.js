import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['grid', 'loader', 'searchInput', 'spinner', 'loaderText', 'statsBar', 'statsShowing', 'statsTotal', 'statsOwned', 'hideCompletedToggle', 'generationSelect'];
    
    static values = {
        url: String,
        variant: { type: String, default: '' },
        search: { type: String, default: '' },
        hideCompleted: { type: Boolean, default: true },
        generation: { type: String, default: '' }
    };

    connect() {
        console.log('Pokédex controller connected');
        this.debounceTimer = null;
        this.allPokemon = []; // Store ALL pokemon from API
        this.filteredPokemon = []; // Filtered pokemon
        this.displayedCount = 0; // How many are currently displayed
        this.perPage = 50; // Display 50 at a time
        this.observer = null;
        this.generations = []; // Store available generations
        
        // Read URL params on load
        this.readURLParams();
        
        // Set active filter button based on current variant
        this.setInitialActiveButton();
        
        // Load all Pokemon once
        this.loadAllPokemon();
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
        this.generationValue = params.get('generation') || '';
        
        // Default to true if not in URL
        const hideCompletedParam = params.get('hideCompleted');
        this.hideCompletedValue = hideCompletedParam === null ? true : hideCompletedParam === 'true';
        
        // Update search input if present
        if (this.hasSearchInputTarget && this.searchValue) {
            this.searchInputTarget.value = this.searchValue;
        }
        
        // Sync hideCompleted checkbox
        if (this.hasHideCompletedToggleTarget) {
            this.hideCompletedToggleTarget.checked = this.hideCompletedValue;
        }
    }

    async loadAllPokemon() {
        this.showLoading();

        try {
            const response = await fetch(this.urlValue, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            this.allPokemon = data.pokemon;
            this.generations = data.generations || [];
            
            // Populate generation select
            this.populateGenerationSelect();
            
            // Apply filters and display
            this.applyFiltersAndDisplay();

        } catch (error) {
            console.error('Error loading Pokémon:', error);
            this.showError();
        } finally {
            this.hideLoading();
        }
    }

    applyFiltersAndDisplay() {
        // Apply all filters to allPokemon
        this.filteredPokemon = this.allPokemon.filter(p => {
            // Generation filter
            if (this.generationValue && p.generation !== this.generationValue) {
                return false;
            }

            // Search filter
            if (this.searchValue) {
                const search = this.searchValue.toLowerCase();
                
                // Check if search is purely numeric
                const isNumericSearch = /^\d+$/.test(this.searchValue);
                
                if (isNumericSearch) {
                    // For numeric search: exact match on Pokemon number ONLY
                    const matchesNumber = p.number === parseInt(this.searchValue, 10);
                    if (!matchesNumber) return false;
                } else {
                    // For text search: search in name only
                    const matchesName = p.name.toLowerCase().includes(search);
                    if (!matchesName) return false;
                }
                
                // When searching, ignore hideCompleted filter - always show search results
                return true;
            }

            // Variant filter (available variants)
            if (this.variantValue) {
                if (this.variantValue === 'shadow' || this.variantValue === 'purified') {
                    if (!p.availableVariants.shadow) return false;
                } else if (this.variantValue === 'shiny') {
                    if (!p.availableVariants.shiny) return false;
                } else if (this.variantValue === 'lucky') {
                    if (!p.availableVariants.lucky) return false;
                }
            }

            // Hide completed filter (only apply when NOT searching)
            if (this.hideCompletedValue) {
                if (this.isPokemonCompleted(p)) return false;
            }

            return true;
        });

        // Reset display
        this.displayedCount = 0;
        this.gridTarget.innerHTML = '';
        
        // Display first page
        this.displayMore();
        
        // Update stats
        this.updateStatsBar();
        
        // Setup infinite scroll
        this.setupIntersectionObserver();
    }

    displayMore() {
        const start = this.displayedCount;
        const end = Math.min(start + this.perPage, this.filteredPokemon.length);
        
        if (start >= end) {
            this.hideLoader();
            return;
        }

        const toDisplay = this.filteredPokemon.slice(start, end);
        
        toDisplay.forEach(p => {
            this.gridTarget.insertAdjacentHTML('beforeend', this.createPokemonCard(p));
        });

        this.displayedCount = end;

        // Check if more to load
        if (this.displayedCount >= this.filteredPokemon.length) {
            this.hideLoader();
        } else {
            this.showLoader();
        }
    }

    filter(event) {
        event.preventDefault();
        const button = event.currentTarget;
        const variant = button.dataset.variant || '';

        // Update active state on buttons
        this.updateActiveFilterButton(button);
        
        this.variantValue = variant;

        // Show/hide stats bar based on filter
        this.toggleStatsBar();

        this.updateURL();
        this.applyFiltersAndDisplay();
    }

    search(event) {
        clearTimeout(this.debounceTimer);
        
        this.debounceTimer = setTimeout(() => {
            this.searchValue = event.target.value.trim();
            this.updateURL();
            this.applyFiltersAndDisplay();
        }, 300);
    }

    async toggleVariant(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const button = event.currentTarget;
        const pokemonId = parseInt(button.dataset.pokemonId);
        const variant = button.dataset.variant;
        const currentlyOwned = button.dataset.owned === 'true';
        const newValue = !currentlyOwned;

        // Optimistic UI update
        this.updateVariantUI(button, newValue);

        try {
            const response = await fetch(`/api/user-pokemon/${pokemonId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    variant: variant,
                    value: newValue
                })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                // Update data-owned attribute
                button.dataset.owned = newValue ? 'true' : 'false';
                
                // Update the Pokemon in allPokemon array with fresh data from API
                const pokemon = this.allPokemon.find(p => p.id === pokemonId);
                if (pokemon) {
                    // Use data from API to ensure consistency
                    pokemon.userPokemon = data.data || pokemon.userPokemon;
                }
                
                // Update the Pokemon in filteredPokemon array too
                const filteredPokemon = this.filteredPokemon.find(p => p.id === pokemonId);
                if (filteredPokemon) {
                    filteredPokemon.userPokemon = data.data || filteredPokemon.userPokemon;
                }
                
                // Update completion badge
                this.updateCompletionBadge(pokemonId, data.data);
                
                // Update stats
                this.updateStatsBar();
                
                // Hide card if completed and hideCompleted is on
                if (this.hideCompletedValue && pokemon) {
                    const card = this.gridTarget.querySelector(`[data-pokemon-id="${pokemonId}"]`);
                    if (card && this.isPokemonCompleted(pokemon)) {
                        card.style.transition = 'opacity 0.3s ease-out';
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            this.displayedCount--;
                        }, 300);
                    }
                }
            } else {
                // Revert UI on error
                this.updateVariantUI(button, currentlyOwned);
            }

        } catch (error) {
            console.error('Error toggling variant:', error);
            // Revert UI on error
            this.updateVariantUI(button, currentlyOwned);
        }
    }

    getVariantKey(variant) {
        return {
            'normal': 'hasNormal',
            'shiny': 'hasShiny',
            'shadow': 'hasShadow',
            'purified': 'hasPurified',
            'lucky': 'hasLucky',
            'xxl': 'hasXxl',
            'xxs': 'hasXxs',
            'perfect': 'hasPerfect'
        }[variant];
    }

    updateVariantUI(button, owned) {
        const img = button.querySelector('img');
        const isFilteredView = this.variantValue !== '';
        const sizeClass = isFilteredView ? 'w-12 h-12' : 'aspect-square';
        
        if (owned) {
            button.className = `${sizeClass} bg-indigo-50 dark:bg-indigo-900/30 ring-2 ring-indigo-500 rounded flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600 transition cursor-pointer`;
            if (img) img.className = 'w-5 h-5 opacity-100 pointer-events-none';
            button.title = button.title.replace(' (owned)', '') + ' (owned)';
        } else {
            button.className = `${sizeClass} bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600 transition cursor-pointer`;
            if (img) img.className = 'w-5 h-5 opacity-30 pointer-events-none';
            button.title = button.title.replace(' (owned)', '');
        }
    }

    updateCompletionBadge(pokemonId, userPokemonData) {
        const card = this.gridTarget.querySelector(`[data-pokemon-id="${pokemonId}"]`);
        if (!card || !userPokemonData) return;

        // Determine border based on context
        let borderClass;
        if (this.variantValue) {
            const variantKey = this.getVariantKey(this.variantValue);
            if (!variantKey) {
                // Unknown variant, use default border
                borderClass = 'border-2 border-gray-200 dark:border-gray-700';
            } else {
                const isOwnedVariant = userPokemonData[variantKey];
                borderClass = isOwnedVariant 
                    ? 'border-2 border-emerald-600/60 dark:border-emerald-500/50' 
                    : 'border-2 border-gray-200 dark:border-gray-700';
            }
        } else {
            const completed = userPokemonData.hasNormal && userPokemonData.hasShiny && 
                             userPokemonData.hasShadow && userPokemonData.hasPurified && 
                             userPokemonData.hasLucky && userPokemonData.hasXxl && 
                             userPokemonData.hasXxs && userPokemonData.hasPerfect;
            
            borderClass = completed 
                ? 'border-2 border-emerald-600/60 dark:border-emerald-500/50' 
                : 'border-2 border-gray-200 dark:border-gray-700';
        }

        // Update card border
        card.className = `bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-xl transition-all overflow-hidden ${borderClass}`;
    }

    createPokemonCard(pokemon) {
        const number = String(pokemon.number).padStart(4, '0');
        const userPokemon = pokemon.userPokemon;
        
        // Calculate completion (all 8 variants owned)
        const completed = userPokemon ? (
            userPokemon.hasNormal &&
            userPokemon.hasShiny &&
            userPokemon.hasShadow &&
            userPokemon.hasPurified &&
            userPokemon.hasLucky &&
            userPokemon.hasXxl &&
            userPokemon.hasXxs &&
            userPokemon.hasPerfect
        ) : false;

        // Determine border based on context
        let borderClass;
        if (this.variantValue && userPokemon) {
            // When filtering by variant, show border if that specific variant is owned
            const variantKey = {
                'normal': 'hasNormal',
                'shiny': 'hasShiny',
                'shadow': 'hasShadow',
                'purified': 'hasPurified',
                'lucky': 'hasLucky',
                'xxl': 'hasXxl',
                'xxs': 'hasXxs',
                'perfect': 'hasPerfect'
            }[this.variantValue];
            
            const isOwnedVariant = variantKey && userPokemon[variantKey];
            borderClass = isOwnedVariant 
                ? 'border-2 border-emerald-600/60 dark:border-emerald-500/50' 
                : 'border-2 border-gray-200 dark:border-gray-700';
        } else {
            // On "All" filter, show border only if all 8 variants are completed
            borderClass = completed 
                ? 'border-2 border-emerald-600/60 dark:border-emerald-500/50' 
                : 'border-2 border-gray-200 dark:border-gray-700';
        }

        // Determine image path based on variant filter
        const imagePath = this.variantValue === 'shiny' 
            ? `/images/pokemon/shiny/${pokemon.picture}` 
            : `/images/pokemon/normal/${pokemon.picture}`;

        // Add lucky background class if on lucky variant filter
        const luckyBackgroundClass = this.variantValue === 'lucky' ? 'lucky-bg' : '';

        // Add shadow/purified icon overlay based on variant filter (bottom-left)
        let variantIconOverlay = '';
        if (this.variantValue === 'shadow') {
            variantIconOverlay = '<img src="/images/icon/shadow.png" alt="Shadow" class="absolute bottom-4 left-14 w-8 h-8 opacity-90 drop-shadow-lg z-10">';
        } else if (this.variantValue === 'purified') {
            variantIconOverlay = '<img src="/images/icon/purified.png" alt="Purified" class="absolute bottom-4 left-14 w-8 h-8 opacity-90 drop-shadow-lg z-10">';
        } else if (this.variantValue === 'shiny') {
            variantIconOverlay = '<img src="/images/icon/shiny.png" alt="Shiny" class="absolute top-8 left-14 w-8 h-8 opacity-90 drop-shadow-lg z-10">';
        }

        return `
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-xl transition-all overflow-hidden ${borderClass} ${luckyBackgroundClass}" data-pokemon-id="${pokemon.id}">
                <div class="relative p-4">
                    <div class="absolute top-2 left-2 px-2 py-0.5 bg-gray-900/80 backdrop-blur-sm rounded-full">
                        <span class="text-xs font-bold text-white">#${number}</span>
                    </div>
                    ${variantIconOverlay}
                    <div class="flex justify-center pt-4">
                        <img src="${imagePath}" 
                             alt="${pokemon.name}"
                             loading="lazy"
                             class="w-28 h-28 object-contain drop-shadow-md">
                    </div>
                </div>
                <div class="px-3 pb-2 text-center">
                    <h3 class="font-semibold text-gray-800 dark:text-white text-sm">
                        ${this.escapeHtml(pokemon.name)}
                    </h3>
                </div>
                <div class="px-3 pb-3">
                    <div class="${this.variantValue ? 'flex justify-center' : 'grid grid-cols-4 gap-1.5'}">
                        ${this.createVariantBadges(pokemon.id, userPokemon, this.variantValue)}
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

    createVariantBadges(pokemonId, userPokemon, currentVariant = '') {
        const variants = [
            { slug: 'normal', icon: 'normal.png', hasKey: 'hasNormal' },
            { slug: 'shiny', icon: 'shiny.png', hasKey: 'hasShiny' },
            { slug: 'shadow', icon: 'shadow.png', hasKey: 'hasShadow' },
            { slug: 'purified', icon: 'purified.png', hasKey: 'hasPurified' },
            { slug: 'lucky', icon: 'lucky.png', hasKey: 'hasLucky' },
            { slug: 'xxl', icon: 'xxl.png', hasKey: 'hasXxl' },
            { slug: 'xxs', icon: 'xxs.png', hasKey: 'hasXxs' },
            { slug: 'perfect', icon: 'perfect.png', hasKey: 'hasPerfect' }
        ];
        
        // Filter variants based on current filter
        const filteredVariants = currentVariant 
            ? variants.filter(v => v.slug === currentVariant)
            : variants;
        
        return filteredVariants.map(variant => {
            const owned = userPokemon && userPokemon[variant.hasKey];
            const opacityClass = owned ? 'opacity-100' : 'opacity-30';
            const bgClass = owned 
                ? 'bg-indigo-50 dark:bg-indigo-900/30 ring-2 ring-indigo-500' 
                : 'bg-gray-200 dark:bg-gray-700';
            
            // When showing single variant, align left like the grid
            const layoutClass = currentVariant ? 'w-12 h-12' : '';
            
            return `
                <div class="${layoutClass} aspect-square ${bgClass} rounded flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600 transition cursor-pointer" 
                     title="${variant.slug}${owned ? ' (owned)' : ''}"
                     data-action="click->pokedex#toggleVariant"
                     data-pokemon-id="${pokemonId}"
                     data-variant="${variant.slug}"
                     data-owned="${owned ? 'true' : 'false'}">
                    <img src="/images/pokedex/${variant.icon}" alt="${variant.slug}" class="w-5 h-5 ${opacityClass} pointer-events-none">
                </div>
            `;
        }).join('');
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

    setupIntersectionObserver() {
        if (!this.hasLoaderTarget) return;

        // Disconnect existing observer
        if (this.observer) {
            this.observer.disconnect();
        }

        this.observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && this.displayedCount < this.filteredPokemon.length) {
                this.displayMore();
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
        if (this.generationValue) params.set('generation', this.generationValue);
        
        // Always set hideCompleted to be explicit
        params.set('hideCompleted', this.hideCompletedValue ? 'true' : 'false');

        const url = params.toString() ? `?${params}` : '';
        window.history.pushState({}, '', `/pokedex${url}`);
    }

    toggleStatsBar() {
        if (!this.hasStatsBarTarget) return;
        
        if (this.variantValue === '') {
            // Hide stats bar on "All" filter
            this.statsBarTarget.classList.add('hidden');
        } else {
            // Show stats bar for specific variants
            this.statsBarTarget.classList.remove('hidden');
        }
    }

    updateStatsBar() {
        if (!this.hasStatsBarTarget) return;
        
        // Calculate showing count (filtered results)
        const uniqueNumbersFiltered = new Set(this.filteredPokemon.map(p => p.number));
        const showingCount = uniqueNumbersFiltered.size;
        
        // Calculate TOTAL count (all Pokemon with this variant available, ignoring search/hideCompleted)
        const totalUnique = this.calculateTotalForVariant();
        
        // Update showing count
        if (this.hasStatsShowingTarget) {
            this.statsShowingTarget.textContent = showingCount;
        }
        if (this.hasStatsTotalTarget) {
            this.statsTotalTarget.textContent = totalUnique;
        }
        
        // Calculate owned count
        if (this.hasStatsOwnedTarget) {
            const ownedCount = this.calculateOwnedCount();
            this.statsOwnedTarget.textContent = ownedCount;
        }
    }

    calculateTotalForVariant() {
        if (!this.variantValue) return 0;

        // Count unique Pokemon numbers that have this variant available
        const uniqueNumbers = new Set();
        
        this.allPokemon.forEach(p => {
            // Check if Pokemon matches variant availability
            if (this.variantValue === 'shadow' || this.variantValue === 'purified') {
                if (!p.availableVariants.shadow) return;
            } else if (this.variantValue === 'shiny') {
                if (!p.availableVariants.shiny) return;
            } else if (this.variantValue === 'lucky') {
                if (!p.availableVariants.lucky) return;
            }
            
            uniqueNumbers.add(p.number);
        });

        return uniqueNumbers.size;
    }

    calculateOwnedCount() {
        if (!this.variantValue) return 0;

        const variantKey = this.getVariantKey(this.variantValue);
        if (!variantKey) return 0;

        // Group by Pokemon number and count unique numbers where at least one form has the variant owned
        const ownedNumbers = new Set();
        
        this.allPokemon.forEach(p => {
            // Check if Pokemon matches variant availability
            if (this.variantValue === 'shadow' || this.variantValue === 'purified') {
                if (!p.availableVariants.shadow) return;
            } else if (this.variantValue === 'shiny') {
                if (!p.availableVariants.shiny) return;
            } else if (this.variantValue === 'lucky') {
                if (!p.availableVariants.lucky) return;
            }
            
            // If user owns this variant for this form, add the Pokemon number to the set
            if (p.userPokemon && p.userPokemon[variantKey]) {
                ownedNumbers.add(p.number);
            }
        });

        return ownedNumbers.size;
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

    toggleHideCompleted(event) {
        this.hideCompletedValue = event.target.checked;
        this.updateURL();
        this.applyFiltersAndDisplay();
    }

    isPokemonCompleted(pokemon) {
        const userPokemon = pokemon.userPokemon;
        
        if (!userPokemon) return false;
        
        // On "All" view: completed means all 8 variants owned
        if (!this.variantValue || this.variantValue === '') {
            return userPokemon.hasNormal && userPokemon.hasShiny && 
                   userPokemon.hasShadow && userPokemon.hasPurified && 
                   userPokemon.hasLucky && userPokemon.hasXxl && 
                   userPokemon.hasXxs && userPokemon.hasPerfect;
        }
        
        // On specific variant view: completed means that variant is owned
        const variantKey = this.getVariantKey(this.variantValue);
        return variantKey && userPokemon[variantKey];
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

    populateGenerationSelect() {
        if (!this.hasGenerationSelectTarget || !this.generations.length) return;

        // Clear existing options except "All Gen"
        this.generationSelectTarget.innerHTML = '<option value="">All Generations</option>';

        // Add generation options (already sorted by backend)
        this.generations.forEach(gen => {
            const option = document.createElement('option');
            option.value = gen;
            // Capitalize first letter of each word
            option.textContent = gen.charAt(0).toUpperCase() + gen.slice(1);
            option.selected = this.generationValue === gen;
            this.generationSelectTarget.appendChild(option);
        });
    }

    filterGenerationFromSelect(event) {
        const generation = event.target.value;
        this.generationValue = generation;
        this.updateURL();
        this.applyFiltersAndDisplay();
    }
}
