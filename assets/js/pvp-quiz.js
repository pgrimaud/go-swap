if (document.querySelector('#pvp-question')) {
    function handleClick(event) {
        resetAnswers();
        event.currentTarget.classList.add('bg-slate-700', 'text-white');
        event.currentTarget.classList.remove('hover:bg-gray-100', 'text-black');
        document.querySelector('#answer-id').value = event.currentTarget.dataset.questionId;
    }

    document.querySelectorAll('.pvp-answer').forEach(item => {
        item.addEventListener('click', handleClick);
    });

    function resetAnswers() {
        document.querySelectorAll('.pvp-answer').forEach(item => {
            item.classList.remove('bg-slate-700')
            item.classList.add('hover:bg-gray-100', 'text-black')
        })
    }

    function handleSubmit() {
        const answerId = document.querySelector('#answer-id').value
        const questionId = document.querySelector('#question-id').value

        if (answerId === '') {
            return
        }

        const data = new FormData();
        data.append('answer', answerId);

        fetch(`/pvp/quiz/question/submit/${questionId}`, {
            method: 'POST',
            body: data,
        })
            .then(response => response.json())
            .then((data) => {
                // remove event listeners
                document.querySelectorAll('.pvp-answer').forEach(item => {
                    item.removeEventListener('click', handleClick);
                });

                document.querySelector('#question-submit').removeEventListener('click', handleSubmit);

                if (data.correct === true) {
                    document.querySelector(`.pvp-answer[data-question-id="${answerId}"]`).classList.remove('bg-slate-700')
                    document.querySelector(`.pvp-answer[data-question-id="${answerId}"]`).classList.add('bg-green-500')
                } else {
                    document.querySelector(`.pvp-answer[data-question-id="${data.goodAnswer}"]`).classList.remove('bg-slate-700')
                    document.querySelector(`.pvp-answer[data-question-id="${data.goodAnswer}"]`).classList.add('bg-red-500')
                }

                document.querySelector('#question-submit').classList.add('hidden')
                document.querySelector('#question-next').classList.remove('hidden')
            })
    }

    document.querySelector('#question-submit').addEventListener('click', handleSubmit);
}