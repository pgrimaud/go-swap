if (document.querySelector('#list-pvp')) {

    const pvpAddButton = document.querySelector('#pvp-add-button');
    const pokemonIdSelect = document.querySelector('#pokemon-id');
    const movesContainer = document.querySelector('#pvp-moves');
    const selectFastMove = document.querySelector('#fast-move');
    const selectChargedMove1 = document.querySelector('#charged-move-1');
    const selectChargedMove2 = document.querySelector('#charged-move-2');
    const leagueContainer = document.querySelector('#pvp-league');
    const saveBtn = document.querySelector('#pvp-save-pokemon');

    if (pvpAddButton) {
        pvpAddButton.addEventListener('click', () => {
            pokemonIdSelect.selectedIndex = 0;
            movesContainer.classList.add('hidden')
            leagueContainer.classList.add('hidden')
            document.querySelector('#pvp-rank').value = '';
            displayModal(document.getElementById('pvp-add'));
        });
    }

    if (pokemonIdSelect) {
        pokemonIdSelect.addEventListener('change', async (event) => {
            const el = event.currentTarget;
            if (el.value === '') {
                return;
            }

            const data = new FormData();
            data.append('id', el.value);

            try {
                const responseData = await fetchApi(data, '/moves-from-pokemon');
                movesContainer.classList.remove('hidden');
                // fast
                setOptionToSelect('#fast-move', responseData.fast)
                // charged 1
                setOptionToSelect('#charged-move-1', responseData.charged)
                // charged 2
                setOptionToSelect('#charged-move-2', responseData.charged)

            } catch (error) {
                console.error('Error', error);
            }
        });
    }

    selectFastMove.addEventListener('change', checkMoves);
    selectChargedMove1.addEventListener('change', checkMoves);
    selectChargedMove2.addEventListener('change', checkMoves);
    saveBtn.addEventListener('click', savePokemon);

    async function savePokemon() {
        const data = new FormData();
        data.append('pokemonId', document.querySelector('#pokemon-id').value);
        data.append('fastMove', document.querySelector('#fast-move').value);
        data.append('chargedMove1', document.querySelector('#charged-move-1').value);
        data.append('chargedMove2', document.querySelector('#charged-move-2').value);
        data.append('league', document.querySelector('#pvp-pokemon-league').value);
        data.append('rank', document.querySelector('#pvp-rank').value);
        data.append('isShadow', document.querySelector('#pvp-is-shadow').checked);

        const responseData = await fetchApi(data, '/pvp/add');

        if (responseData.status === 'ok') {
            location.reload();
        } else {
            alert('And error occured!');
        }
    }

    function checkMoves() {
        if (selectFastMove.value !== '' && selectChargedMove1.value !== '') {
            leagueContainer.classList.remove('hidden');
        } else {
            leagueContainer.classList.add('hidden');
        }
    }

    async function fetchApi(data, url) {
        const response = await fetch(url, {
            method: 'POST',
            body: data,
        });
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    }

    function setOptionToSelect(selector, choices) {
        document.querySelector(selector).innerHTML = '<option value="">-</option>';
        choices.forEach(choice => {
            document.querySelector(selector).innerHTML += `<option value="${choice.id}">${choice.name}${choice.elite === true ? ' (Elite TM)' : ''}</option>`;
        });
    }

    function displayModal(modal) {
        const body = document.body;

        modal.classList.remove('hidden', 'opacity-0');
        modal.classList.add('opacity-100');
        modal.querySelector('.transform').classList.remove('scale-95');
        modal.querySelector('.transform').classList.add('scale-100');
        body.style.overflow = 'hidden';

        const closeModal = modal.querySelector('.closeModal');
        if (closeModal) {
            closeModal.addEventListener('click', () => {
                modal.classList.add('opacity-0');
                modal.classList.remove('opacity-100');
                modal.querySelector('.transform').classList.add('scale-95');
                modal.querySelector('.transform').classList.remove('scale-100');
                modal.classList.add('hidden');
                body.style.overflow = 'auto';
            });
        }
    }
}
