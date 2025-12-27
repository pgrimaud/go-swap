# 🔄 Pokédex AJAX Refactoring Plan

> **Date**: 2025-12-27  
> **Status**: Planning Phase  
> **Estimated Time**: ~3h30

---

## 📋 Table of Contents

1. [Context & Objectives](#context--objectives)
2. [Current Issues](#current-issues)
3. [Technical Architecture](#technical-architecture)
4. [Implementation Phases](#implementation-phases)
5. [Code Examples](#code-examples)
6. [Testing Strategy](#testing-strategy)
7. [Migration Checklist](#migration-checklist)

---

## 🎯 Context & Objectives

### Current State
- Pokédex page loads **1044 Pokémon** at once
- Every filter change triggers **full page reload**
- No pagination
- Heavy DOM (performance issues on mobile)
- No shareable filtered URLs

### Goals
- ✅ AJAX filtering without page reload
- ✅ Infinite scroll pagination (20-50 Pokémon per page)
- ✅ Shareable URLs with filters (`/pokedex?variant=shiny`)
- ✅ Home page cards link to filtered Pokédex
- ✅ Optimized performance
- ✅ Better UX with loading states

---

## ❌ Current Issues

### Performance
- **1044 DOM nodes** on initial load
- Heavy on mobile devices
- Long initial load time
- Memory usage

### UX
- Page reload = loss of scroll position
- No loading feedback
- Can't share filtered view
- Slow filter interactions

### Code
- Controller handles too much logic
- No API endpoint
- Tightly coupled view/data

---

## 🏗️ Technical Architecture

### Stack
- **Backend**: Symfony 8.0 + Doctrine
- **Frontend**: Hotwire (Turbo + Stimulus)
- **Styles**: TailwindCSS v4
- **Assets**: AssetMapper

### New Components

```
Backend:
├── PokedexController::apiPokedex()     (JSON API)
├── Pokemon Entity serialization groups
└── Pagination logic

Frontend:
├── assets/controllers/pokedex_controller.js  (Stimulus)
├── templates/pokedex/_card.html.twig        (Partial)
└── URL state management
```

---

## 🔧 Implementation Phases

### Phase 1: Backend API Endpoint (30 min)

**Goal**: Create JSON API for Pokémon data

**File**: `src/Controller/PokedexController.php`

**Tasks**:
1. Create `/api/pokedex` route
2. Add pagination logic (page, perPage)
3. Apply filters (variant, search)
4. Return JSON response with metadata

**Response Format**:
```json
{
  "pokemon": [...],
  "page": 1,
  "perPage": 50,
  "total": 1044,
  "hasMore": true,
  "variant": "shiny",
  "search": ""
}
```

**Key Points**:
- Use Doctrine `setFirstResult()` / `setMaxResults()`
- Return serialized Pokemon entities
- Include pagination metadata
- Handle filters same as current controller

---

### Phase 2: Entity Serialization (15 min)

**Goal**: Prepare Pokemon entity for JSON serialization

**File**: `src/Entity/Pokemon.php`

**Tasks**:
1. Add `#[Groups(['pokemon:read'])]` attributes
2. Include: id, number, name, picture, generation
3. Serialize types relationship
4. Test serialization output

**Example**:
```php
#[ORM\Column]
#[Groups(['pokemon:read'])]
private int $id;

#[ORM\Column]
#[Groups(['pokemon:read'])]
private int $number;

#[ORM\ManyToMany(targetEntity: Type::class)]
#[Groups(['pokemon:read'])]
private Collection $types;
```

---

### Phase 3: Stimulus Controller (1h)

**Goal**: Handle AJAX requests, filters, and infinite scroll

**File**: `assets/controllers/pokedex_controller.js`

**Features**:
- Fetch Pokémon from API
- Handle filter changes
- Infinite scroll with Intersection Observer
- Update URL without reload
- Loading states
- Error handling

**State Management**:
```javascript
static values = {
    url: String,           // API endpoint
    page: Number,          // Current page
    loading: Boolean,      // Loading state
    hasMore: Boolean,      // More pages?
    variant: String,       // Current filter
    search: String         // Search query
}
```

**Key Methods**:
- `connect()`: Initialize, read URL params, load first page
- `filter(event)`: Handle filter button clicks
- `search(event)`: Handle search input
- `loadPokemon(append)`: Fetch from API
- `appendPokemon(data)`: Add to grid
- `replacePokemon(data)`: Replace grid content
- `updateURL()`: Update browser URL
- `setupIntersectionObserver()`: Infinite scroll

---

### Phase 4: Template Refactoring (30 min)

**Goal**: Make template AJAX-ready

**File**: `templates/pokedex/index.html.twig`

**Changes**:
1. Add Stimulus controller
2. Transform form to AJAX
3. Empty grid (filled by JS)
4. Add loader element

**Structure**:
```twig
<div data-controller="pokedex" 
     data-pokedex-url-value="{{ path('api_pokedex') }}"
     data-pokedex-variant-value="{{ currentVariant }}"
     data-pokedex-search-value="{{ currentSearch }}">
    
    {# Filters #}
    <div data-pokedex-target="filters">
        <button data-action="click->pokedex#filter" 
                data-variant="normal">Normal</button>
        {# ... other filters #}
    </div>
    
    {# Search #}
    <input data-pokedex-target="searchInput"
           data-action="input->pokedex#search">
    
    {# Grid (empty, filled by JS) #}
    <div data-pokedex-target="grid" 
         class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
        {# Cards injected here #}
    </div>
    
    {# Infinite scroll trigger #}
    <div data-pokedex-target="loader">
        <div class="animate-spin">⏳ Loading...</div>
    </div>
</div>
```

---

### Phase 5: Pokemon Card Partial (20 min)

**Goal**: Reusable card template or JS template

**Option A - Twig Partial**: `templates/pokedex/_card.html.twig`

```twig
<div class="bg-white dark:bg-gray-800 rounded-lg shadow...">
    {# Existing card HTML #}
</div>
```

Then render in JS:
```javascript
const html = await fetch(`/pokedex/card/${pokemon.id}`).text();
grid.insertAdjacentHTML('beforeend', html);
```

**Option B - JS Template** (Recommended):

```javascript
createPokemonCard(pokemon) {
    return `
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow...">
            <div class="px-3 py-2 flex justify-between">
                <span>#${pokemon.number.toString().padStart(4, '0')}</span>
                <span>${pokemon.completed ? '✅' : '⬜'}</span>
            </div>
            <img src="/images/pokemon/normal/${pokemon.picture}" 
                 alt="${pokemon.name}" loading="lazy">
            <h3>${pokemon.name}</h3>
            {# Variant badges #}
        </div>
    `;
}
```

**Decision**: Use **Option B** (faster, no extra HTTP request)

---

### Phase 6: Home Page Links (15 min)

**Goal**: Link home cards to filtered Pokédex

**File**: `templates/home/index.html.twig`

**Changes**:
```twig
{% for category in pokedexCategories %}
<a href="{{ path('app_pokedex', {variant: category.slug}) }}" 
   class="bg-white dark:bg-gray-800 rounded-lg shadow...">
    <img src="{{ asset('images/pokedex/' ~ category.icon) }}">
    <h3>{{ category.name }}</h3>
    <span>{{ category.percentage }}%</span>
    <p>{{ category.count }} / {{ category.total }}</p>
</a>
{% endfor %}
```

**Controller Update**: `src/Controller/HomeController.php`

```php
$pokedexCategories = [
    ['name' => 'Normal', 'slug' => 'normal', 'icon' => 'normal.png', ...],
    ['name' => 'Shiny', 'slug' => 'shiny', 'icon' => 'shiny.png', ...],
    // ...
];
```

---

### Phase 7: UX Improvements (30 min)

**Features**:

#### 1. Skeleton Loaders
```twig
<div data-pokedex-target="skeleton" class="hidden">
    <div class="animate-pulse bg-gray-200 h-64 rounded-lg"></div>
    {# Repeat 8 times #}
</div>
```

#### 2. Empty State
```javascript
if (data.pokemon.length === 0) {
    grid.innerHTML = `
        <div class="col-span-full text-center py-12">
            <div class="text-6xl mb-4">🔍</div>
            <h3>No Pokémon found</h3>
        </div>
    `;
}
```

#### 3. End of List Message
```javascript
if (!data.hasMore) {
    grid.insertAdjacentHTML('beforeend', `
        <div class="col-span-full text-center py-8 text-gray-500">
            ✅ You've seen all Pokémon!
        </div>
    `);
}
```

#### 4. Back to Top Button
```javascript
window.addEventListener('scroll', () => {
    if (window.scrollY > 500) {
        backToTopBtn.classList.remove('hidden');
    }
});
```

#### 5. Loading Indicator
```javascript
showLoading() {
    this.loaderTarget.classList.remove('hidden');
}

hideLoading() {
    this.loaderTarget.classList.add('hidden');
}
```

---

## 💻 Code Examples

### Backend Controller

```php
<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\PokemonRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class PokedexController extends AbstractController
{
    #[Route('/api/pokedex', name: 'api_pokedex', methods: ['GET'])]
    public function apiPokedex(
        Request $request,
        PokemonRepository $pokemonRepository,
    ): JsonResponse {
        $variant = $request->query->get('variant', '');
        $search = $request->query->get('search', '');
        $page = $request->query->getInt('page', 1);
        $perPage = 50;

        // Build query
        $queryBuilder = $pokemonRepository->createQueryBuilder('p')
            ->orderBy('p.number', 'ASC')
            ->setFirstResult(($page - 1) * $perPage)
            ->setMaxResults($perPage);

        // Apply search filter
        if ($search !== '') {
            $queryBuilder
                ->andWhere('p.name LIKE :search OR p.number = :number')
                ->setParameter('search', '%' . $search . '%')
                ->setParameter('number', (int) $search);
        }

        // TODO: Apply variant filter when UserPokemon entity is ready

        $pokemon = $queryBuilder->getQuery()->getResult();
        $total = $pokemonRepository->count([]);

        return $this->json([
            'pokemon' => $pokemon,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'hasMore' => ($page * $perPage) < $total,
            'variant' => $variant,
            'search' => $search,
        ], 200, [], ['groups' => ['pokemon:read']]);
    }

    #[Route('/pokedex', name: 'app_pokedex')]
    public function index(Request $request): Response
    {
        // Just render template, data loaded via AJAX
        return $this->render('pokedex/index.html.twig', [
            'currentVariant' => $request->query->get('variant', ''),
            'currentSearch' => $request->query->get('search', ''),
        ]);
    }
}
```

---

### Stimulus Controller (Complete)

```javascript
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['grid', 'loader', 'searchInput', 'skeleton'];
    
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
        
        // Initial load
        this.loadPokemon(false);
        
        // Setup infinite scroll
        this.setupIntersectionObserver();
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
    }

    readURLParams() {
        const params = new URLSearchParams(window.location.search);
        this.variantValue = params.get('variant') || '';
        this.searchValue = params.get('search') || '';
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
            const response = await fetch(`${this.urlValue}?${params}`);
            const data = await response.json();

            if (append) {
                this.appendPokemon(data.pokemon);
            } else {
                this.replacePokemon(data.pokemon);
            }

            this.hasMoreValue = data.hasMore;
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

        this.variantValue = variant;
        this.pageValue = 1;
        this.hasMoreValue = true;

        this.updateURL();
        this.loadPokemon(false);
    }

    search(event) {
        clearTimeout(this.debounceTimer);
        
        this.debounceTimer = setTimeout(() => {
            this.searchValue = event.target.value;
            this.pageValue = 1;
            this.hasMoreValue = true;

            this.updateURL();
            this.loadPokemon(false);
        }, 300);
    }

    replacePokemon(pokemon) {
        if (pokemon.length === 0) {
            this.gridTarget.innerHTML = this.emptyStateHTML();
            return;
        }

        this.gridTarget.innerHTML = '';
        pokemon.forEach(p => {
            this.gridTarget.insertAdjacentHTML('beforeend', this.createPokemonCard(p));
        });
    }

    appendPokemon(pokemon) {
        pokemon.forEach(p => {
            this.gridTarget.insertAdjacentHTML('beforeend', this.createPokemonCard(p));
        });
    }

    createPokemonCard(pokemon) {
        const number = String(pokemon.number).padStart(4, '0');
        const completed = false; // TODO: Real completion check

        return `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-all duration-200 hover:scale-105 cursor-pointer overflow-hidden">
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
                        ${pokemon.name}
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

    createVariantBadges() {
        const variants = ['normal', 'shiny', 'shadow', 'purified', 'lucky', 'xxl', 'xxs', 'perfect'];
        return variants.map(variant => `
            <div class="aspect-square bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center hover:bg-gray-300 dark:hover:bg-gray-600 transition" title="${variant}">
                <img src="/images/pokedex/${variant}.png" alt="${variant}" class="w-5 h-5 opacity-40">
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

    setupIntersectionObserver() {
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

        const url = params.toString() ? `?${params}` : '/pokedex';
        window.history.pushState({}, '', `/pokedex${url}`);
    }

    updateStatsBar(data) {
        // TODO: Update stats bar when variant is "All"
    }

    showLoading() {
        if (this.hasTarget('loader')) {
            this.loaderTarget.classList.remove('hidden');
        }
    }

    hideLoading() {
        if (this.hasTarget('loader')) {
            this.loaderTarget.classList.add('hidden');
        }
    }

    showError() {
        this.gridTarget.innerHTML = `
            <div class="col-span-full text-center py-12">
                <div class="text-6xl mb-4">❌</div>
                <h3 class="text-xl font-semibold text-red-600 mb-2">Error loading Pokémon</h3>
                <button onclick="location.reload()" class="mt-4 px-4 py-2 bg-indigo-600 text-white rounded-lg">
                    Retry
                </button>
            </div>
        `;
    }
}
```

---

## 🧪 Testing Strategy

### Backend Tests

**File**: `tests/Controller/PokedexControllerTest.php`

```php
public function testApiPokedexEndpoint(): void
{
    $client = static::createClient();
    
    // Login
    $this->loginUser($client);
    
    // Call API
    $client->request('GET', '/api/pokedex');
    
    $this->assertResponseIsSuccessful();
    $this->assertResponseHeaderSame('Content-Type', 'application/json');
    
    $data = json_decode($client->getResponse()->getContent(), true);
    
    $this->assertArrayHasKey('pokemon', $data);
    $this->assertArrayHasKey('page', $data);
    $this->assertArrayHasKey('total', $data);
    $this->assertArrayHasKey('hasMore', $data);
}

public function testApiPokedexPagination(): void
{
    $client = static::createClient();
    $this->loginUser($client);
    
    // Page 1
    $client->request('GET', '/api/pokedex?page=1');
    $data1 = json_decode($client->getResponse()->getContent(), true);
    
    // Page 2
    $client->request('GET', '/api/pokedex?page=2');
    $data2 = json_decode($client->getResponse()->getContent(), true);
    
    $this->assertNotEquals($data1['pokemon'][0]['id'], $data2['pokemon'][0]['id']);
}

public function testApiPokedexVariantFilter(): void
{
    $client = static::createClient();
    $this->loginUser($client);
    
    $client->request('GET', '/api/pokedex?variant=shiny');
    
    $this->assertResponseIsSuccessful();
    $data = json_decode($client->getResponse()->getContent(), true);
    
    $this->assertEquals('shiny', $data['variant']);
}
```

### Frontend Tests (Optional - E2E)

Use Panther or Cypress for testing Stimulus behavior

---

## ✅ Migration Checklist

### Backend
- [ ] Create `/api/pokedex` route
- [ ] Add pagination logic (setFirstResult/setMaxResults)
- [ ] Add serialization groups to Pokemon entity
- [ ] Test API endpoint returns correct JSON
- [ ] Add cache headers (Cache-Control)

### Frontend
- [ ] Create `pokedex_controller.js`
- [ ] Implement `loadPokemon()` method
- [ ] Implement `filter()` method
- [ ] Implement `search()` method with debounce
- [ ] Setup Intersection Observer
- [ ] Implement URL state management
- [ ] Create `createPokemonCard()` template function

### Templates
- [ ] Add Stimulus controller to template
- [ ] Transform filters to AJAX
- [ ] Add loader element
- [ ] Add skeleton loaders
- [ ] Update home page cards with links

### UX
- [ ] Add loading states
- [ ] Add empty state message
- [ ] Add "end of list" message
- [ ] Add error handling
- [ ] Test on mobile devices

### Tests
- [ ] Test API endpoint
- [ ] Test pagination
- [ ] Test filters
- [ ] Test search
- [ ] Update existing PokedexControllerTest

### Performance
- [ ] Verify lazy loading images work
- [ ] Check API response time (<200ms)
- [ ] Test with 1044 Pokémon
- [ ] Verify memory usage
- [ ] Add DB indexes if needed

### Documentation
- [ ] Update AGENTS.md with new architecture
- [ ] Document Stimulus controller
- [ ] Update TODO_V3.md

---

## 📊 Performance Targets

| Metric | Current | Target |
|--------|---------|--------|
| Initial Load | 1044 cards | 50 cards |
| DOM Nodes | ~20,000 | ~2,000 |
| Load Time | ~3s | <500ms |
| Filter Time | ~2s (reload) | <100ms |
| Memory Usage | ~150MB | ~50MB |
| API Response | N/A | <200ms |

---

## 🚨 Risks & Mitigation

### Risk 1: Breaking existing functionality
**Mitigation**: Keep old controller method, add new API route, test thoroughly

### Risk 2: SEO impact (no SSR)
**Mitigation**: Initial render with SSR (first 50 Pokémon), then AJAX for rest

### Risk 3: Browser compatibility
**Mitigation**: Use polyfills for Intersection Observer if needed

### Risk 4: Increased complexity
**Mitigation**: Good documentation, clean code, comments

---

## 📝 Notes

- **Turbo**: We're using Stimulus, not full Turbo Frames (simpler for this use case)
- **AssetMapper**: No build step, pure ES6 modules
- **Backward compat**: Old `/pokedex` route still works, just empty on load
- **Progressive enhancement**: Works without JS (initial render)

---

## 🎯 Success Criteria

- ✅ Page loads in <500ms
- ✅ Filtering works without reload
- ✅ Infinite scroll is smooth
- ✅ URLs are shareable
- ✅ Mobile performance improved
- ✅ All tests pass
- ✅ No regressions

---

## 📅 Timeline

**Day 1** (2h):
- Phase 1: Backend API
- Phase 2: Serialization
- Tests

**Day 2** (2h):
- Phase 3: Stimulus controller
- Phase 4: Template refactor

**Day 3** (1h):
- Phase 5: Home links
- Phase 6: UX improvements
- Final tests

**Total**: ~5 hours

---

## 🔗 References

- [Stimulus Handbook](https://stimulus.hotwired.dev/)
- [Symfony Serialization](https://symfony.com/doc/current/serializer.html)
- [Intersection Observer API](https://developer.mozilla.org/en-US/docs/Web/API/Intersection_Observer_API)
- [History API](https://developer.mozilla.org/en-US/docs/Web/API/History_API)

---

## 📝 Implementation Checklist

> **Implementation Status**: Ready to start  
> **Implementer**: AI Agent  
> **Start Date**: 2025-12-27

### Phase 1: Backend API Endpoint ✅
- [ ] Create `/api/pokedex` route in `PokedexController`
- [ ] Add pagination logic with `setFirstResult()` and `setMaxResults()`
- [ ] Apply search filter
- [ ] Return JSON response with metadata (pokemon, page, perPage, total, hasMore)
- [ ] Test endpoint manually (`curl` or browser)
- [ ] Verify JSON structure matches spec

### Phase 2: Entity Serialization ✅
- [ ] Add `#[Groups(['pokemon:read'])]` to Pokemon entity properties
- [ ] Add serialization groups to: id, number, name, picture, generation
- [ ] Serialize types relationship
- [ ] Test serialization output (check JSON response)
- [ ] Fix any circular reference issues

### Phase 3: Stimulus Controller ✅
- [ ] Create `assets/controllers/pokedex_controller.js`
- [ ] Add static targets: grid, loader, searchInput, skeleton
- [ ] Add static values: url, page, loading, hasMore, variant, search
- [ ] Implement `connect()` method
- [ ] Implement `disconnect()` method
- [ ] Implement `readURLParams()` method
- [ ] Implement `loadPokemon(append)` method
- [ ] Implement `filter(event)` method
- [ ] Implement `search(event)` with debounce
- [ ] Implement `replacePokemon(data)` method
- [ ] Implement `appendPokemon(data)` method
- [ ] Implement `createPokemonCard(pokemon)` template
- [ ] Implement `createVariantBadges()` template
- [ ] Implement `emptyStateHTML()` method
- [ ] Implement `setupIntersectionObserver()` method
- [ ] Implement `updateURL()` method
- [ ] Implement `showLoading()` / `hideLoading()` methods
- [ ] Implement `showError()` method
- [ ] Test in browser console

### Phase 4: Template Refactoring ✅
- [ ] Add `data-controller="pokedex"` to main wrapper
- [ ] Add `data-pokedex-url-value="{{ path('api_pokedex') }}"`
- [ ] Add `data-pokedex-variant-value="{{ currentVariant }}"`
- [ ] Add `data-pokedex-search-value="{{ currentSearch }}"`
- [ ] Add `data-pokedex-target="grid"` to grid container
- [ ] Empty grid content (will be filled by JS)
- [ ] Add `data-action="click->pokedex#filter"` to filter buttons
- [ ] Add `data-variant` attribute to each filter button
- [ ] Update search input with `data-pokedex-target="searchInput"`
- [ ] Add `data-action="input->pokedex#search"` to search
- [ ] Add loader element with `data-pokedex-target="loader"`
- [ ] Keep stats bar conditional display logic

### Phase 5: Home Page Links ✅
- [ ] Update `HomeController` to pass `pokedexCategories` array
- [ ] Add `slug` field to each category
- [ ] Wrap category cards in `<a>` tags
- [ ] Add `href="{{ path('app_pokedex', {variant: category.slug}) }}"`
- [ ] Test links navigate to correct filtered page
- [ ] Remove hover:border if inside link

### Phase 6: UX Improvements ✅
- [ ] Add skeleton loaders in template (hidden by default)
- [ ] Show/hide skeleton during loading
- [ ] Add empty state message in JS
- [ ] Add "end of list" message in JS
- [ ] Add back-to-top button (optional)
- [ ] Test loading states
- [ ] Test error states
- [ ] Test on mobile viewport

### Phase 7: Testing ✅
- [ ] Create `testApiPokedexEndpoint()` test
- [ ] Create `testApiPokedexPagination()` test
- [ ] Create `testApiPokedexVariantFilter()` test
- [ ] Create `testApiPokedexSearch()` test
- [ ] Update existing `testPokedexPageIsAccessibleForAuthenticatedUser()`
- [ ] Run all tests: `composer test`
- [ ] Fix any failing tests

### Phase 8: Quality & Performance ✅
- [ ] Run `composer cs-fix`
- [ ] Run `composer phpstan`
- [ ] Test with Chrome DevTools (Network, Performance)
- [ ] Verify images lazy load correctly
- [ ] Check API response time (<200ms)
- [ ] Test infinite scroll smoothness
- [ ] Test on mobile device (real or emulated)
- [ ] Verify memory usage is acceptable

### Phase 9: Documentation ✅
- [ ] Update `AGENTS.md` with new architecture
- [ ] Add note about Stimulus controller usage
- [ ] Update `TODO_V3.md` (mark Phase 1 complete, add Phase 2)
- [ ] Add comments in Stimulus controller
- [ ] Document any gotchas or known issues

### Phase 10: Final Verification ✅
- [ ] Test all filter buttons work without reload
- [ ] Test search works with debounce
- [ ] Test infinite scroll loads more Pokémon
- [ ] Test URLs are shareable (copy/paste in new tab)
- [ ] Test home page links work
- [ ] Test back button works correctly
- [ ] Test with no results (search "zzzz")
- [ ] Test with slow network (DevTools throttling)
- [ ] Verify no console errors
- [ ] Verify no regressions (login, register still work)

---

### 🎯 Completion Criteria

**Definition of Done**:
- ✅ All checkboxes above are checked
- ✅ All tests pass (`composer test`)
- ✅ No CS violations (`composer cs-fix`)
- ✅ No PHPStan errors (`composer phpstan`)
- ✅ Page loads <500ms
- ✅ Filtering works without page reload
- ✅ Infinite scroll is smooth
- ✅ URLs are shareable
- ✅ Mobile tested and working
- ✅ No console errors in browser
- ✅ Documentation updated

---

### 📊 Progress Tracker

| Phase | Status | Time Spent | Notes |
|-------|--------|-----------|-------|
| Phase 1: Backend API | ⏳ Not Started | 0h | - |
| Phase 2: Serialization | ⏳ Not Started | 0h | - |
| Phase 3: Stimulus Controller | ⏳ Not Started | 0h | - |
| Phase 4: Template Refactor | ⏳ Not Started | 0h | - |
| Phase 5: Home Links | ⏳ Not Started | 0h | - |
| Phase 6: UX Improvements | ⏳ Not Started | 0h | - |
| Phase 7: Testing | ⏳ Not Started | 0h | - |
| Phase 8: Quality & Perf | ⏳ Not Started | 0h | - |
| Phase 9: Documentation | ⏳ Not Started | 0h | - |
| Phase 10: Final Verify | ⏳ Not Started | 0h | - |

**Legend**: ⏳ Not Started | 🚧 In Progress | ✅ Complete | ❌ Blocked

---

**Ready to start implementation! Let's go! 🚀**
