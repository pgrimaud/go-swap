import Choices from 'choices.js';

if (document.querySelector('#add-pvp-pokemon')) {
    document.querySelector('#add-pvp-pokemon select[name=pokemon]').addEventListener('change', function () {
        fetch('/api/pokemon/moves/' + this.value, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            },
        }).then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }).then(data => {
            updateMoves('fast-move', data.fastMoves)
            updateMoves('charged-move1', data.chargedMoves)
            updateMoves('charged-move2', data.chargedMoves)
        }).catch(error => {
            console.error('There was a problem with the fetch operation:', error);
        });
    })

    const choices = new Choices('#select-pokemon');

    document.querySelector('#add-pvp-pokemon button[name="confirm"]').addEventListener('click', function () {
        // Vérification des champs obligatoires
        const form = document.querySelector('#add-pvp-pokemon');
        const requiredFields = [
            { selector: 'select[name="pokemon"]', name: 'pokemon' },
            { selector: 'select[name="fast-move"]', name: 'fastMove' },
            { selector: 'select[name="charged-move1"]', name: 'chargedMove1' },
            { selector: 'select[name="league"]', name: 'league' },
            { selector: 'input[name="attack"]', name: 'attack' },
            { selector: 'input[name="stamina"]', name: 'stamina' },
            { selector: 'input[name="defense"]', name: 'defense' },
            { selector: 'input[name="league-rank"]', name: 'leagueRank' },
            { selector: 'select[name="type"]', name: 'type' },
        ];
        let hasError = false;
        requiredFields.forEach(field => {
            const el = form.querySelector(field.selector);
            if (el) {
                if (!el.value) {
                    el.classList.add('border-2', 'border-red-500', 'dark:border-red-500');
                    hasError = true;
                } else {
                    el.classList.remove('border-2', 'border-red-500', 'dark:border-red-500');
                }
            }
        });
        // ChargedMove2 (optionnel)
        const chargedMove2El = form.querySelector('select[name="charged-move2"]');
        if (chargedMove2El) {
            chargedMove2El.classList.remove('border-2', 'border-red-500');
        }
        if (hasError) {
            return;
        }
        // create form data
        const data = new FormData();
        data.append('pokemonId', form.querySelector('select[name="pokemon"]').value);
        data.append('fastMove', form.querySelector('select[name="fast-move"]').value);
        data.append('chargedMove1', form.querySelector('select[name="charged-move1"]').value);
        data.append('chargedMove2', form.querySelector('select[name="charged-move2"]').value);
        data.append('league', form.querySelector('select[name="league"]').value);
        data.append('attack', form.querySelector('input[name="attack"]').value);
        data.append('stamina', form.querySelector('input[name="stamina"]').value);
        data.append('defense', form.querySelector('input[name="defense"]').value);
        data.append('leagueRank', form.querySelector('input[name="league-rank"]').value);
        data.append('type', form.querySelector('select[name="type"]').value);

        fetch('/api/pokemon',
            {
                method: 'POST',
                body: data,
            }
        )
            .then(response => response.json())
            .then(data => {
                window.location.reload()
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
    });

    document.querySelectorAll('.delete-pokemon').forEach(el => {
        el.addEventListener('click', function (event) {
            const pokemonId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this Pokémon?')) {
                window.location.href = '/pvp/pokemon/delete/' + pokemonId;
            }
        });
    })

    // League filter
    document.querySelectorAll('.league-filter').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.league-filter').forEach(b => {
                b.classList.remove('bg-gray-100', 'dark:bg-gray-800');
            });
            this.classList.add('bg-gray-100', 'dark:bg-gray-800');
            const league = this.getAttribute('data-league');
            document.querySelectorAll('.pokemon-row').forEach(row => {
                if (!league || row.getAttribute('data-league') === league) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
}

// Helper to filter rows based on both search and league
function filterPokemonRows() {
    const searchInput = document.querySelector('input[placeholder="Search"]');
    const search = searchInput ? searchInput.value.trim().toLowerCase() : '';
    const activeLeagueBtn = document.querySelector('.league-filter.bg-gray-100, .league-filter.dark.bg-gray-800');
    const activeLeague = activeLeagueBtn ? activeLeagueBtn.getAttribute('data-league') : '';
    document.querySelectorAll('.pokemon-row').forEach(row => {
        const name = row.querySelector('h2')?.textContent?.toLowerCase() || '';
        const number = row.getAttribute('data-number') || '';
        const moves = Array.from(row.querySelectorAll('td:nth-child(4) span')).map(e => e.textContent.toLowerCase()).join(' ');
        const league = row.getAttribute('data-league') || '';
        let matchesSearch = false;
        if (!search) {
            matchesSearch = true;
        } else if (!isNaN(search) && search === number) {
            // Si la recherche est un nombre, on veut une correspondance exacte sur le numéro
            matchesSearch = true;
        } else if (name.includes(search) || moves.includes(search)) {
            matchesSearch = true;
        }
        const matchesLeague = !activeLeague || league === activeLeague;
        if (matchesSearch && matchesLeague) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

const searchInput = document.querySelector('input[placeholder="Search"]');
if (searchInput) {
    searchInput.addEventListener('input', filterPokemonRows);
}
document.querySelectorAll('.league-filter').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.league-filter').forEach(b => {
            b.classList.remove('bg-gray-100', 'dark:bg-gray-800');
        });
        this.classList.add('bg-gray-100', 'dark:bg-gray-800');
        filterPokemonRows();
    });
});

function updateMoves(target, moves) {
    const movesSelect = document.querySelector(`#add-pvp-pokemon select[name=${target}]`);

    if (movesSelect) {
        movesSelect.disabled = false;
        movesSelect.innerHTML = '<option></option>';
        if (moves && moves.length > 0) {
            moves.forEach(move => {
                const option = document.createElement('option');
                option.value = move.id;
                option.textContent = `${move.name}${move.isElite ? ' (Elite)' : ''}`;
                movesSelect.appendChild(option);
            });
        } else {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No moves available';
            movesSelect.appendChild(option);
        }
    }
}

function sortPokemonTable(by, direction = 'asc') {
    const tbody = document.querySelector('tbody');
    const rows = Array.from(document.querySelectorAll('.pokemon-row'));
    rows.sort((a, b) => {
        let valA, valB;
        if (by === 'name') {
            // Trie par numéro si on trie par nom
            valA = parseInt(a.getAttribute('data-number'), 10) || 0;
            valB = parseInt(b.getAttribute('data-number'), 10) || 0;
        } else if (by === 'rank') {
            valA = parseInt(a.querySelector('td:nth-child(2) .inline')?.textContent?.trim() || '9999', 10);
            valB = parseInt(b.querySelector('td:nth-child(2) .inline')?.textContent?.trim() || '9999', 10);
        }
        if (valA < valB) return direction === 'asc' ? -1 : 1;
        if (valA > valB) return direction === 'asc' ? 1 : -1;
        return 0;
    });
    rows.forEach(row => tbody.appendChild(row));
}

let currentSort = { by: null, direction: 'asc' };
document.querySelectorAll('.sort-header').forEach(header => {
    header.addEventListener('click', function () {
        const by = this.getAttribute('data-sort');
        let direction = 'asc';
        if (currentSort.by === by && currentSort.direction === 'asc') {
            direction = 'desc';
        }
        currentSort = { by, direction };
        sortPokemonTable(by, direction);
        // Met à jour l'indicateur visuel
        document.querySelectorAll('.sort-indicator').forEach(i => i.textContent = '');
        this.querySelector('.sort-indicator').textContent = direction === 'asc' ? '▲' : '▼';
    });
});
