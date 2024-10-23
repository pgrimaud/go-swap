if (document.querySelector('#switch-pokedex')) {
    document.querySelector('#switch-pokedex').addEventListener('change', event => {
        if (event.target.value !== 'View friend\'s Pokédex') {
            window.location.href = '/pokedex-friend/' + event.target.value
        }
    })
}

if (document.querySelector('#isValid')) {
    displayModal(document.getElementById('isValid'))
}

if (document.querySelector('.form-submission')) {
    document.querySelector('.form-submission').addEventListener('submit', () => {
        displayModal(document.getElementById('loading'))
    });
}

function displayModal(modal) {
    const body = document.body;

    modal.classList.remove('hidden');
    modal.classList.remove('opacity-0');
    modal.classList.add('opacity-100');
    modal.querySelector('.transform').classList.remove('scale-95');
    modal.querySelector('.transform').classList.add('scale-100');
    body.style.overflow = 'hidden';

    if (modal.querySelector('.closeModal')) {
        modal.querySelector('.closeModal').addEventListener('click', () => {
            modal.classList.add('opacity-0');
            modal.classList.remove('opacity-100');
            modal.querySelector('.transform').classList.add('scale-95');
            modal.querySelector('.transform').classList.remove('scale-100');
            modal.classList.add('hidden');
            body.style.overflow = 'auto';
        });
    }
}

if (document.querySelector('.button-details')) {
    document.querySelectorAll('.button-details').forEach(button => {
        button.addEventListener('click', () => {
            displayModal(document.getElementById(`modal-details-${button.getAttribute('data-type')}`))
        })
    });
}