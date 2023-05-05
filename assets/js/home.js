if (document.querySelector('#switch-pokedex')) {
    document.querySelector('#switch-pokedex').addEventListener('change', event => {
        if (event.target.value !== 'View friend\'s Pokédex') {
            window.location.href = '/pokedex-friend/' + event.target.value
        }
    })
}