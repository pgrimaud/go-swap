if (document.querySelector('#list-pvp')) {
    let parameters = {
        'search': document.querySelector('#search-pvp').value.toLowerCase(),
        'displayHidden': document.querySelector('#display-hidden').checked,
        'onlyOwned': document.querySelector('#only-owned').checked,
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

    // display only owned
    document.querySelector('#only-owned').addEventListener('change', (e) => {
        parameters.onlyOwned = e.currentTarget.checked
        filter()
    })

    // rank inputs
    document.querySelectorAll('.rank-input').forEach(el => {
        el.addEventListener('change', () => {
            changeInputColor(el, parseInt(el.value));

            const data = new FormData();
            data.append('id', el.dataset.internalId);
            data.append('rank', el.value);
            data.append('league',el.dataset.league);

            fetchApi(data, '/pvp/update')

            // probably to hide ?
            const row = el.closest('.pokemon-row');
            let hasRank = false;

            row.querySelectorAll('.rank-input').forEach(elInput => {
                if (!isNaN(parseInt(elInput.value)) || parseInt(elInput.value) > 0) {
                    hasRank = true;
                }
            })

            if (hasRank === false) {
                row.dataset.owned = '0';
                filter();
            } else {
                row.dataset.owned = '1';
            }
        })
    })

    // hidden btn
    document.querySelectorAll('.btn-hide').forEach(el => {
        el.addEventListener('click', () => {
            const toHide = el.classList.contains('bg-red-500') ? 1 : 0;
            const data = new FormData();
            data.append('id', el.dataset.internalId);
            data.append('hidden', toHide);

            if (toHide) {
                el.innerHTML = 'Unhide';
                el.classList.remove('bg-red-500', 'hover:bg-red-700');
                el.classList.add('bg-green-500', 'hover:bg-green-700');
                el.closest('.pokemon-row').classList.add('hidden');
                el.closest('.pokemon-row').dataset.pokemonHidden = 1;
            } else {
                el.innerHTML = 'Hide';
                el.classList.remove('bg-green-500', 'hover:bg-green-700');
                el.classList.add('bg-red-500', 'hover:bg-red-700');
                el.closest('.pokemon-row').dataset.pokemonHidden = 0;
            }

            fetchApi(data, '/pvp/display');
        })
    })

    function filter() {
        // reset rows
        displayPokemonAllRows(true)

        // filter search
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

        if (parameters.onlyOwned === true) {
            document.querySelectorAll('.pokemon-row[data-owned="0"]').forEach(el => {
                el.classList.add('hidden')
            })
        }
    }

    function displayPokemonAllRows(reset) {
        const selector = parameters.displayHidden === true ? '.pokemon-row' : '.pokemon-row[data-pokemon-hidden="0"]'
        document.querySelectorAll(selector).forEach(el => el.classList.toggle('hidden', !reset))

        if (parameters.displayHidden === false) {
            document.querySelectorAll('.pokemon-row[data-pokemon-hidden="1"]').forEach(el => el.classList.add('hidden'))
        }
    }

    function displayPokemonRows(selector, reset) {
        document.querySelectorAll(selector).forEach(el => el.classList.toggle('hidden', !reset))
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

    function changeInputColor(element, rank) {
        element.classList.remove('border-gray-700', 'border-blue-500', 'border-green-500', 'border-yellow-500', 'border-red-500', 'border-back');
        element.classList.remove('bg-slate-600', 'bg-blue-700', 'bg-green-700', 'bg-yellow-700', 'bg-red-700', 'bg-black');

        if (rank === 0 || isNaN(rank)) {
            element.classList.add('border-gray-500', 'bg-slate-600');
            return;
        }

        const classMap = {
            1: ['border-blue-500', 'bg-blue-700'],
            10: ['border-green-500', 'bg-green-700'],
            30: ['border-yellow-500', 'bg-yellow-700'],
            100: ['border-red-500', 'bg-red-700'],
            4096: ['border-back', 'bg-black'],
        };

        for (const [maxRank, classes] of Object.entries(classMap)) {
            if (rank <= maxRank) {
                element.classList.add(...classes);
                return;
            }
        }
    }
}