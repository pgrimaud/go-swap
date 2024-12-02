if (document.querySelector('#list-pvp')) {
    let parameters = {
        'search': document.querySelector('#search-pvp').value.toLowerCase(),
        'displayHidden': document.querySelector('#display-hidden').checked,
    }

    filter()

    /**
     * EVENTS
     */

    // input search
    document.querySelector('#search-pvp').addEventListener('keyup', (e) => {
        parameters.search = e.currentTarget.value.toLowerCase()
        filter()
    })

    // display hidden pokemon
    document.querySelector('#display-hidden').addEventListener('change', (e) => {
        parameters.displayHidden = e.currentTarget.checked
        filter()
    })

    function filter() {
        console.log(parameters)

        // reset rows
        displayPokemonAllRows(true)

        // filter searc
        if (/^\d+$/.test(parameters.search) === true) {
            displayPokemonAllRows(false)
            displayPokemonRows(`.pokemon-row[data-number='${parameters.search}']`, true)
        } else if (parameters.search === '') {
            // nothing to do
        } else {
            displayPokemonAllRows(false)
            document.querySelectorAll(`.pokemon-row`).forEach(el => {
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
    }

    function displayPokemonAllRows(reset) {
        document.querySelectorAll('.pokemon-row').forEach(el => el.classList.toggle('hidden', !reset))
    }

    function displayPokemonRows(selector, reset) {
        document.querySelectorAll(selector).forEach(el => el.classList.toggle('hidden', !reset))
    }
}