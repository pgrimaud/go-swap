if (document.querySelector('#search')) {
    let parameters = {
        'search': document.querySelector('#search').value.toLowerCase(),
        'pokedex': document.querySelector('#filters .active-filter').getAttribute('id'),
        'hideCaught': document.querySelector('#hide-caught').checked,
        'onlyActual': document.querySelector('#only-actual').checked,
    }

    filter()

    /**
     * EVENTS
     */

    // input search
    document.querySelector('#search').addEventListener('keyup', (e) => {
        parameters.search = e.currentTarget.value.toLowerCase()
        filter()
    })

    // pokédex type filters
    document.querySelectorAll('#filters button').forEach(el => {
        el.addEventListener('click', (e) => {
            // reset all buttons style
            document.querySelectorAll('#filters button').forEach(el => {
                el.classList.remove('from-pink-400', 'to-purple-600', 'active-filter')
                el.classList.add('bg-slate-700')
            })

            e.target.classList.add('from-pink-400', 'to-purple-600', 'active-filter')
            window.history.replaceState({}, '', `?type=${e.target.getAttribute('id')}`)
            parameters.pokedex = el.getAttribute('id')

            filter()
        })
    })
    // pokédex type filters on mobile
    document.querySelector('#mobile-filters').addEventListener('change', (elem) => {
        window.history.replaceState({}, '', `?type=${elem.target.value}`)
        parameters.pokedex = elem.target.value

        filter()
    })

    // hide caught pokémon
    document.querySelector('#hide-caught').addEventListener('change', (e) => {
        parameters.hideCaught = e.currentTarget.checked
        filter()
    })

    // display only actual pokémon
    document.querySelector('#only-actual').addEventListener('change', (e) => {
        parameters.onlyActual = e.currentTarget.checked
        filter()
    })

    // add or remove pokémon to a pokédex
    document.querySelectorAll('#pokedex .poke-card-user').forEach(el => {
        if (!window.location.pathname.includes('/pokedex-friend')) {
            el.addEventListener('click', () => {
                const data = new FormData();
                data.append('id', el.dataset.internalId);
                data.append('pokedex', parameters.pokedex);

                if (el.classList.contains('pokemon-caught')) {
                    el.setAttribute(`data-pokedex-${parameters.pokedex}`, 0)
                    fetchApi(data, '/delete')
                    if (parameters.pokedex === 'shiny') {
                        el.querySelector('.custom-input-number').value = 0
                    }

                    displayCardAsCaught(el, false)
                } else {
                    el.setAttribute(`data-pokedex-${parameters.pokedex}`, 1)
                    fetchApi(data, '/add')
                    if (parameters.pokedex === 'shiny') {
                        el.querySelector('.custom-input-number').value = 1
                    }

                    displayCardAsCaught(el, true)
                    displayNoPokemonFound(document.querySelectorAll('#pokedex .poke-card:not(.hidden)').length === 0)
                    filter()
                }
            })
        }
    })
    //Scroll to the right generation
    document.querySelectorAll('*[name=selectGeneration]').forEach(btn => {
        btn.addEventListener('change', (event => {
            goToGeneration(event.target.value);
        }));
    });

    //Scroll back to the top
    document.querySelector('#scrollToTop').addEventListener('click', () => {
        goToGeneration('page-top')
    })

    // counters
    document.querySelectorAll('.custom-number-input button').forEach(btn => {
        btn.addEventListener('click', (event => {
            event.stopPropagation()
            changeCounter(btn)
        }));
    });

    /**
     * METHODS
     */
    function changeCounter(btn) {
        if (btn.dataset.action === 'increment') {
            btn.previousElementSibling.value = parseInt(btn.previousElementSibling.value) + 1

            const data = new FormData();
            data.append('id', btn.closest('.poke-card-user').dataset.internalId);
            data.append('value', btn.previousElementSibling.value);

            btn.closest('.poke-card-user').setAttribute(`data-pokedex-shiny`, 1)

            fetchApi(data, '/shiny')
        } else {
            if (parseInt(btn.nextElementSibling.value) > 0) {
                btn.nextElementSibling.value = parseInt(btn.nextElementSibling.value) - 1

                const data = new FormData();
                data.append('id', btn.closest('.poke-card-user').dataset.internalId);
                data.append('value', btn.nextElementSibling.value);

                if (parseInt(btn.nextElementSibling.value) === 0) {
                    btn.closest('.poke-card-user').setAttribute(`data-pokedex-shiny`, 0)
                }

                fetchApi(data, '/shiny')
            }
        }
        filter()
    }

    function filter() {
        // hide container
        document.querySelector('#pokedex').classList.add('hidden')

        // reset cards
        displayAllGeneration()
        displayNoPokemonFound(false)
        displayAllPokemonCards(true)

        // display pokémon as caught
        document.querySelectorAll('.poke-card-user').forEach(el => {
            displayCardAsCaught(el, el.getAttribute(`data-pokedex-${parameters.pokedex}`) === '1')
        })

        // filter search
        if (/^\d+$/.test(parameters.search) === true) {
            displayAllPokemonCards(false)
            displayPokemonCards(`#pokedex .poke-card[data-number='${parameters.search}']`, true)
        } else if (parameters.search === '') {
            // nothing to do
        } else {
            displayAllPokemonCards(false)
            document.querySelectorAll(`#pokedex .poke-card`).forEach(el => {
                if (
                    el.dataset.nameFr.includes(parameters.search) === true ||
                    el.dataset.nameEn.includes(parameters.search) === true ||
                    el.dataset.chainFr.includes(parameters.search) === true ||
                    el.dataset.chainEn.includes(parameters.search) === true
                ) {
                    el.classList.remove('hidden')
                }
            })
        }

        // hide or display 'caught' pokémon => input checkbox
        if (parameters.hideCaught === true) {
            displayPokemonCards(`#pokedex .poke-card.pokemon-caught`, false)
        }

        // hide or display 'caught' pokémon => input checkbox
        if (parameters.onlyActual === true) {
            displayPokemonCards(`#pokedex .poke-card.pokemon-is-not-actual`, false)
        }

        // manage shiny counter
        displayShinyCounter(parameters.pokedex)

        displayPokedexCardType(parameters.pokedex)

        displayNoPokemonFound(document.querySelectorAll('#pokedex .poke-card:not(.hidden)').length === 0)

        hideGenerations()

        // display container
        document.querySelector('#pokedex').classList.remove('hidden')
    }

    function displayShinyCounter(pokedex) {
        document.querySelectorAll('.custom-number-input').forEach(el => {
            el.classList.toggle('hidden', pokedex !== 'shiny')
        })
    }

    function displayPokemonCards(selector, reset) {
        document.querySelectorAll(selector).forEach(el => el.classList.toggle('hidden', !reset))
    }

    function displayAllPokemonCards(reset) {
        document.querySelectorAll('#pokedex .poke-card').forEach(el => el.classList.toggle('hidden', !reset))
    }

    function displayNoPokemonFound(reset) {
        document.querySelector('#pokedex .no-pokemon').classList.toggle('hidden', !reset);
    }

    function displayCardAsCaught(el, reset) {
        el.classList.toggle('pokemon-caught', reset)
        el.classList.toggle('border-green-600', reset)
        el.classList.toggle('border-gray-400', !reset)
        el.classList.toggle('border-opacity-20', !reset)
    }

    function displayPokedexCardType(type) {
        // reset cards
        document.querySelectorAll('.background-lucky, .shiny-picture, .shiny-icon, .purified-icon, .shadow-icon').forEach(el => el.classList.add('hidden'))
        document.querySelectorAll('.normal-picture').forEach(el => el.classList.remove('hidden'))

        if (type === 'shiny') {
            document.querySelectorAll('.shiny-picture, .shiny-icon').forEach(el => el.classList.remove('hidden'))
            document.querySelectorAll('.normal-picture').forEach(el => el.classList.add('hidden'))

            document.querySelectorAll('.poke-card[data-shiny=""]').forEach(el => el.classList.add('hidden'))
        } else if (['lucky', 'shadow', 'purified'].includes(type)) {
            if (type === 'lucky') {
                document.querySelectorAll('.background-lucky').forEach(el => el.classList.remove('hidden'))
            } else if (type === 'shadow') {
                document.querySelectorAll(`.shadow-icon`).forEach(el => el.classList.remove('hidden'))
            } else if (type === 'purified') {
                document.querySelectorAll(`.purified-icon`).forEach(el => el.classList.remove('hidden'))
            }

            document.querySelectorAll(`.poke-card[data-${type}=""]`).forEach(el => el.classList.add('hidden'))
        }
    }

    function fetchApi(data, url) {
        fetch(url, {
            method: 'POST',
            body: data,
        })
            .then(response => response.json())
            .then(() => {
            })
    }

    function goToGeneration(gen) {
        document.getElementById(gen).scrollIntoView({behavior: 'smooth', block: 'start'});
    }

    function displayAllGeneration() {
        document.querySelectorAll('.generation').forEach(el => el.classList.remove('hidden'))
    }

    function hideGenerations() {
        document.querySelectorAll('.generation').forEach(el => {
            let generation = el.getAttribute('id');
            if (document.querySelectorAll(`#pokedex .poke-card[data-generation='${generation}']:not(.hidden)`).length === 0) {
                if (document.getElementById(`${generation}`)) {
                    document.getElementById(`${generation}`).classList.add('hidden')
                }
            }
        })
    }
}
