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

        if (event.target.getAttribute('id') === 'shiny') {
            document.querySelectorAll('.shiny-picture').forEach(el => {
                el.classList.remove('hidden')
            })

            document.querySelectorAll('.normal-picture').forEach(el => {
                el.classList.add('hidden')
            })
        } else {
            document.querySelectorAll('.shiny-picture').forEach(el => {
                el.classList.add('hidden')
            })

            document.querySelectorAll('.normal-picture').forEach(el => {
                el.classList.remove('hidden')
            })
        }

    })
})

document.querySelector('#search').addEventListener('keyup', (event) => {
    const search = event.target.value.toLowerCase()

    // filter only on numbers
    if (/^\d+$/.test(search) === true) {
        document.querySelectorAll('#pokedex .poke-card').forEach(el => {
            el.classList.add('hidden')
        })
        document.querySelector(`#pokedex .poke-card[data-number="${search}"]`).classList.remove('hidden')
    } else if (search === '') {
        document.querySelectorAll('#pokedex .poke-card').forEach(el => {
            el.classList.remove('hidden')
        })
    } else {
        document.querySelectorAll('#pokedex .poke-card').forEach(el => {
            el.classList.add('hidden')
        })
        document.querySelectorAll(`#pokedex .poke-card`).forEach(el => {
            if (el.dataset.nameFr.includes(search) === true || el.dataset.nameEn.includes(search) === true) {
                el.classList.remove('hidden')
            }
        })
    }
})

document.querySelectorAll('#pokedex .poke-card').forEach(el => {

    el.addEventListener('click', (event) => {
        const data = new FormData();
        data.append('id', el.dataset.number);
        data.append('pokedex', document.querySelector('.active-filter').getAttribute('id'));

        if (el.classList.contains('pokemon-catched')) {
            fetchApi(data, "http://127.0.0.1:8000/delete")
        } else {
            fetchApi(data, "http://127.0.0.1:8000/add")

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