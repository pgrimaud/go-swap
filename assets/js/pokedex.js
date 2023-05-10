if (document.querySelector('#search')) {
    filter()
    document.querySelector('#search').addEventListener('keyup', (event) => filter())
    document.querySelectorAll('#filters button').forEach(el => {
        el.addEventListener('click', (event) => {
            document.querySelectorAll('#filters button').forEach(el => {
                el.classList.remove('from-pink-400', 'to-purple-600', 'active-filter')
                el.classList.add('bg-slate-700')
            })
            event.target.classList.add('from-pink-400', 'to-purple-600', 'active-filter')
            window.history.replaceState({}, '', `?type=${event.target.getAttribute('id')}`)
            filter()
            hidePokemon()

        })
    })

    document.querySelectorAll('#pokedex .poke-card-user').forEach(el => {

        el.addEventListener('click', (event) => {
            const data = new FormData();
            data.append('id', el.dataset.internalId);
            data.append('pokedex', document.querySelector('.active-filter').getAttribute('id'));

            if (el.classList.contains('pokemon-catched')) {
                el.setAttribute(`data-pokedex-${document.querySelector('.active-filter').getAttribute('id')}`, 0)
                fetchApi(data, "/delete")
                el.classList.add('border-gray-400', 'border-opacity-20')
                el.classList.remove('pokemon-catched', 'border-green-600')
            } else {
                el.setAttribute(`data-pokedex-${document.querySelector('.active-filter').getAttribute('id')}`, 1)
                fetchApi(data, "/add")
                el.classList.add('pokemon-catched', 'border-green-600')
                el.classList.remove('border-gray-400', 'border-opacity-20')
            }
        })
    })
    document.querySelectorAll('.poke-card').forEach(el => {
        document.querySelectorAll('#filters button').forEach(filter => {

            if (el.dataset.pokedex === filter.getAttribute('id')) {
                el.classList.remove('hidden')
            }
        })
    })

    // add border to pokemon catched
    document.querySelectorAll('.poke-card-user').forEach(el => {
        const pokedex = document.querySelector('#filters button.to-purple-600').getAttribute('id')

        if (el.getAttribute(`data-pokedex-${pokedex}`) === '1') {
            el.classList.add('border-green-600', 'pokemon-catched')
            el.classList.remove('border-gray-400', 'border-opacity-20')
        }
    })

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

function filter() {

    // reset no pokemon found
    document.querySelector('#pokedex .no-pokemon').classList.add('hidden')

    const search = document.querySelector('#search').value.toLowerCase()

    // filter only on numbers
    if (/^\d+$/.test(search) === true) {
        document.querySelectorAll('#pokedex .poke-card').forEach(el => el.classList.add('hidden'))
        document.querySelector(`#pokedex .poke-card[data-number="${search}"]`).classList.remove('hidden')

    } else if (search === '') {
        // reset to display all
        document.querySelectorAll('#pokedex .poke-card').forEach(el => el.classList.remove('hidden'))

    } else {

        document.querySelectorAll('#pokedex .poke-card').forEach(el => el.classList.add('hidden'))
        document.querySelectorAll(`#pokedex .poke-card`).forEach(el => {
            if (el.dataset.nameFr.includes(search) === true || el.dataset.nameEn.includes(search) === true) {
                el.classList.remove('hidden')
            }
        })


    }
    hidePokemon()
    // find pokedex selected
    const pokedex = document.querySelector('#filters button.to-purple-600').getAttribute('id')

    // reset pokedex
    document.querySelectorAll('.background-lucky').forEach(el => el.classList.add('hidden'))
    document.querySelectorAll('.shiny-picture').forEach(el => el.classList.add('hidden'))
    document.querySelectorAll('.normal-picture').forEach(el => el.classList.remove('hidden'))
    document.querySelectorAll('.shiny-icon').forEach(el => el.classList.add('hidden'))

    if (pokedex === 'shiny') {
        document.querySelectorAll('.shiny-picture').forEach(el => el.classList.remove('hidden'))
        document.querySelectorAll('.normal-picture').forEach(el => el.classList.add('hidden'))
        document.querySelectorAll('.shiny-icon').forEach(el => el.classList.remove('hidden'))

        document.querySelectorAll('.poke-card').forEach(el => {
            if (el.dataset.shiny !== '1') {
                el.classList.add('hidden')
            }
        })
    } else if (pokedex === 'lucky') {
        document.querySelectorAll('.background-lucky').forEach(el => el.classList.remove('hidden'))
    }

    // if no pokemon found
    if (document.querySelectorAll('#pokedex .poke-card:not(.hidden)').length === 0) {
        document.querySelector('#pokedex .no-pokemon').classList.remove('hidden')
    }

    document.querySelectorAll('.poke-card').forEach(el => {
        const pokedex = document.querySelector('#filters button.to-purple-600').getAttribute('id')

        if (el.getAttribute(`data-pokedex-${pokedex}`) === '1') {
            el.classList.add('border-green-600', 'pokemon-catched')
            el.classList.remove('border-gray-400', 'border-opacity-20')
        } else {
            el.classList.remove('border-green-600', 'pokemon-catched')
            el.classList.add('border-gray-400', 'border-opacity-20')

        }
    })


}

document.querySelector('#toggleCatchPokemons').addEventListener('click', hidePokemon)

function hidePokemon() {
    let catchedPokemon = document.querySelectorAll(".pokemon-catched")
    const pokedex = document.querySelector('#filters button.to-purple-600').getAttribute('id')

    if (document.querySelector('#toggleCatchPokemons').checked) {
        catchedPokemon.forEach(el => {

            if (el.getAttribute(`data-pokedex-${pokedex}`) === '1') {
                el.classList.add('hidden')
            }
        })
    } else {

        catchedPokemon.forEach(el => {

            if (el.getAttribute(`data-pokedex-${pokedex}`) === '1') {
                let search = document.querySelector('#search').value

                if (search !== "") {

                    if (el.dataset.nameFr.includes(search.toLowerCase()) || el.dataset.number.includes(search)) {
                        el.classList.remove('hidden')
                        document.querySelector(".no-pokemon").classList.add('hidden')
                    } else {
                        el.classList.add('hidden')
                    }
                } else {
                    el.classList.remove('hidden')
                }
            }
        })
    }
}

