if (document.querySelector('#search')) {
    document.querySelector('#search').addEventListener('keyup', (event) => filter())
    document.querySelectorAll('#filters button').forEach(el => {
        el.addEventListener('click', (event) => {
            document.querySelectorAll('#filters button').forEach(el => {
                el.classList.remove('from-pink-400')
                el.classList.remove('to-purple-600')
                el.classList.remove("active-filter")
                el.classList.add('bg-slate-700')
            })

            event.target.classList.add('from-pink-400')
            event.target.classList.add('to-purple-600')
            event.target.classList.add("active-filter")

            filter()
        })
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

    document.querySelectorAll('#pokedex .poke-card').forEach(el => {

        el.addEventListener('click', (event) => {
            const data = new FormData();
            data.append('id', el.dataset.number);
            data.append('pokedex', document.querySelector('.active-filter').getAttribute('id'));

            if (el.classList.contains('pokemon-catched')) {
                fetchApi(data, "/delete")
            } else {
                fetchApi(data, "/add")

                el.classList.add('pokemon-catched')
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

    function fetchApi(data, url) {
        fetch(url, {
            method: "POST",
            body: data,
        })
            .then(response => response.json())
            .then(json => console.log(json))
    }
}