if (document.querySelector('#search')) {
    let parameters = {
        'search': document.querySelector('#search').value,
        'pokedex': document.querySelector('#filters .active-filter').getAttribute('id'),
        'hideCaught': document.querySelector('#hide-caught').checked
    }

    filter()

    /**
     * EVENTS
     */

    // input search
    document.querySelector('#search').addEventListener('keyup', (e) => {
        parameters.search = e.currentTarget.value
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

    // hide caught pokémon
    document.querySelector('#hide-caught').addEventListener('change', (e) => {
        parameters.hideCaught = e.currentTarget.checked
        filter()
    })

    // add or remove pokémon to a pokédex
    document.querySelectorAll('#pokedex .poke-card-user').forEach(el => {
        el.addEventListener('click', (event) => {
            const data = new FormData();
            data.append('id', el.dataset.internalId);
            data.append('pokedex', parameters.pokedex);

            if (el.classList.contains('pokemon-caught')) {
                el.setAttribute(`data-pokedex-${parameters.pokedex}`, 0)
                fetchApi(data, "/delete")

                displayCardAsCaught(el, false)
            } else {
                el.setAttribute(`data-pokedex-${parameters.pokedex}`, 1)
                fetchApi(data, "/add")

                displayCardAsCaught(el, true)
                displayNoPokemonFound(document.querySelectorAll('#pokedex .poke-card:not(.hidden)').length === 0)
                filter()
            }
        })
    })

    /**
     * METHODS
     */

    function filter() {
        // reset cards
        displayNoPokemonFound(false)
        displayAllPokemonCards(true)
        displayPokedexCardType(parameters.pokedex)

        // display pokémon as caught
        document.querySelectorAll('.poke-card-user').forEach(el => {
            displayCardAsCaught(el, el.getAttribute(`data-pokedex-${parameters.pokedex}`) === '1')
        })

        // filter search
        if (/^\d+$/.test(parameters.search) === true) {
            displayAllPokemonCards(false)
            displayPokemonCards(`#pokedex .poke-card[data-number="${parameters.search}"]`, true)
        } else if (parameters.search === '') {
            // nothing to do
        } else {
            displayAllPokemonCards(false)
            document.querySelectorAll(`#pokedex .poke-card`).forEach(el => {
                if (
                    el.dataset.nameFr.includes(parameters.search) === true ||
                    el.dataset.nameEn.includes(parameters.search) === true
                ) {
                    el.classList.remove('hidden')
                }
            })
        }

        // hide or display "caught" pokémon => input checkbox
        if (parameters.hideCaught === true) {
            displayPokemonCards(`#pokedex .poke-card.pokemon-caught`, false)
        }

        displayNoPokemonFound(document.querySelectorAll('#pokedex .poke-card:not(.hidden)').length === 0)
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
        document.querySelectorAll('.background-lucky, .shiny-picture, .shiny-icon').forEach(el => el.classList.add('hidden'))
        document.querySelectorAll('.normal-picture').forEach(el => el.classList.remove('hidden'))

        if (type === 'shiny') {
            document.querySelectorAll('.shiny-picture, .shiny-icon').forEach(el => el.classList.remove('hidden'))
            document.querySelectorAll('.normal-picture').forEach(el => el.classList.add('hidden'))

            document.querySelectorAll('.poke-card [data-shiny="1"]').forEach(el => el.classList.add('hidden'))
        } else if (type === 'lucky') {
            document.querySelectorAll('.background-lucky').forEach(el => el.classList.remove('hidden'))
        }
    }

    function fetchApi(data, url) {
        fetch(url, {
            method: "POST",
            body: data,
        })
            .then(response => response.json())
            .then(json => {
            })
    }
}