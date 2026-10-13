// assets/js/script.js

document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('voteForm');
    if (!form) return;

    const candidateCards = document.querySelectorAll('.candidate-card');
    const hiddenInput    = document.getElementById('selectedCandidateId');
    const confirmText    = document.getElementById('confirmText');
    const submitBtn      = document.getElementById('submitVote');

    candidateCards.forEach(card => {
        const btn = card.querySelector('.select-btn');
        btn.addEventListener('click', () => {
            // Clear previous selection
            candidateCards.forEach(c => c.classList.remove('selected'));

            // Select this card
            card.classList.add('selected');

            const id   = card.dataset.id;
            const name = card.querySelector('h3').textContent;
            const party = card.querySelector('.party').textContent;

            hiddenInput.value = id;
            confirmText.innerHTML = `You selected: <strong>${name}</strong> (${party})`;
            submitBtn.disabled = false;
        });
    });

    // Final confirmation before submit
    form.addEventListener('submit', (e) => {
        if (!hiddenInput.value) {
            e.preventDefault();
            alert('Please select a candidate first.');
            return;
        }
        const name = confirmText.textContent;
        if (!confirm(`Are you sure you want to submit your vote?\n\n${name}\n\nYou cannot change it later.`)) {
            e.preventDefault();
        }
    });
});

// ====== Live Election Countdown ======
(function () {
    const countdownEls = document.querySelectorAll('[data-countdown]');
    if (!countdownEls.length) return;

    function updateCountdowns() {
        const now = new Date().getTime();

        countdownEls.forEach(el => {
            const target = new Date(el.dataset.countdown).getTime();
            const diff = target - now;

            if (diff <= 0) {
                el.textContent = 'Ended';
                el.classList.add('countdown-ended');
                return;
            }

            const days    = Math.floor(diff / (1000 * 60 * 60 * 24));
            const hours   = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((diff % (1000 * 60)) / 1000);

            el.textContent = (days > 0 ? days + 'd ' : '') +
                             String(hours).padStart(2, '0') + 'h ' +
                             String(minutes).padStart(2, '0') + 'm ' +
                             String(seconds).padStart(2, '0') + 's';
        });
    }

    updateCountdowns();
    setInterval(updateCountdowns, 1000);
})();

// ====== Password Strength Meter ======
(function () {
    const input = document.getElementById('passwordInput');
    if (!input) return;

    const bar  = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');

    input.addEventListener('input', function () {
        const val = input.value;
        let score = 0;

        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        const levels = [
            { pct: 0,   color: '#e2e8f0', label: 'Enter a password' },
            { pct: 20,  color: '#dc2626', label: 'Very weak' },
            { pct: 40,  color: '#f97316', label: 'Weak' },
            { pct: 60,  color: '#eab308', label: 'Fair' },
            { pct: 80,  color: '#22c55e', label: 'Strong' },
            { pct: 100, color: '#16a34a', label: 'Very strong' }
        ];

        const level = levels[score];
        bar.style.width = level.pct + '%';
        bar.style.background = level.color;
        text.textContent = level.label;
        text.style.color = level.color;
    });
})();