if (document.querySelector('#search')) {
    let parameters = {
        'search': document.querySelector('#search').value.toLowerCase(),
        'pokedex': document.querySelector('#filters .active-filter').getAttribute('id'),
        'hideCaught': document.querySelector('#hide-caught').checked
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

    // hide caught pokémon
    document.querySelector('#hide-caught').addEventListener('change', (e) => {
        parameters.hideCaught = e.currentTarget.checked
        filter()
    })

    // add or remove pokémon to a pokédex
    document.querySelectorAll('#pokedex .poke-card-user').forEach(el => {
        if (!window.location.pathname.includes('/pokedex-friend')) {
            el.addEventListener('click', (event) => {
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
    document.querySelector('#selectGeneration').addEventListener('change', (e) => {
        goToGeneration(document.querySelector('#selectGeneration').value);
    })

    //Scroll back to the top
    document.querySelector('#scrollToTop').addEventListener('click', (e) => {
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

                if(btn.nextElementSibling.value === '0') {
                    btn.closest('.poke-card-user').setAttribute(`data-pokedex-shiny`, 0)
                }

                fetchApi(data, '/shiny')
            }
        }
        filter()
    }

    function filter() {
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
                    el.dataset.nameEn.includes(parameters.search) === true
                ) {
                    el.classList.remove('hidden')
                }
            })
        }

        // hide or display 'caught' pokémon => input checkbox
        if (parameters.hideCaught === true) {
            displayPokemonCards(`#pokedex .poke-card.pokemon-caught`, false)
        }


        // manage shiny counter
        displayShinyCounter(parameters.pokedex)

        displayPokedexCardType(parameters.pokedex)

        displayNoPokemonFound(document.querySelectorAll('#pokedex .poke-card:not(.hidden)').length === 0)

        hideGenerations()
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
        document.querySelectorAll('.background-lucky, .shiny-picture, .shiny-icon').forEach(el => el.classList.add('hidden'))
        document.querySelectorAll('.normal-picture').forEach(el => el.classList.remove('hidden'))

        if (type === 'shiny') {
            document.querySelectorAll('.shiny-picture, .shiny-icon').forEach(el => el.classList.remove('hidden'))
            document.querySelectorAll('.normal-picture').forEach(el => el.classList.add('hidden'))

            document.querySelectorAll('.poke-card[data-shiny=""]').forEach(el => el.classList.add('hidden'))
        } else if (type === 'lucky') {
            document.querySelectorAll('.background-lucky').forEach(el => el.classList.remove('hidden'))
        }
    }

    function fetchApi(data, url) {
        fetch(url, {
            method: 'POST',
            body: data,
        })
            .then(response => response.json())
            .then(json => {
            })
    }

    function goToGeneration(gen) {
        document.getElementById(gen).scrollIntoView({behavior: 'smooth', block: 'start'});
    }

    function displayAllGeneration() {
        document.querySelectorAll('.generation').forEach(el => el.classList.remove('hidden'))
    }

    function hideGenerations() {
        for (let i = 1; i <= 10; i++) {
            if (document.querySelectorAll(`#pokedex .poke-card[data-generation='${i}G']:not(.hidden)`).length === 0) {
                if (document.getElementById(`${i}G`)) {
                    document.getElementById(`${i}G`).classList.add('hidden')
                }
            }
        }
    }

}
