<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-auction-countdown]').forEach(function (element) {
            var raw = element.dataset.auctionCountdown;

            if (!raw) {
                return;
            }

            var end = new Date(raw).getTime();

            var render = function () {
                var distance = end - Date.now();

                if (distance <= 0) {
                    if (element.textContent === '{{ __('Calculating') }}') {
                        element.textContent = '{{ __('Not allocated yet') }}';
                    }

                    return;
                }

                var days = Math.floor(distance / 86400000);
                var hours = Math.floor((distance % 86400000) / 3600000);
                var minutes = Math.floor((distance % 3600000) / 60000);

                element.textContent = days
                    ? days + 'd ' + String(hours).padStart(2, '0') + 'h'
                    : String(hours).padStart(2, '0') + 'h ' + String(minutes).padStart(2, '0') + 'm';
            };

            render();
            setInterval(render, 60000);
        });
    });
</script>
