<x-layouts.app :header="false" :hero="false" :breadcrumbs="false" :footer="false">
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="max-w-md w-full mx-auto p-8 text-center">
            <div class="mb-8">
                <svg class="animate-spin h-16 w-16 mx-auto text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                Betaling wordt verwerkt...
            </h1>

            <p class="text-gray-600 mb-6">
                We wachten op bevestiging van je betaling. Dit kan enkele seconden duren.
            </p>

            <p class="text-sm text-gray-500">
                Sluit dit venster niet. Je wordt automatisch doorgestuurd.
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        (function() {
            const orderId = @json($order_id);
            const orderKey = @json($order_key);
            const checkUrl = '{{ route('payment.return', ['order_id' => $order_id]) }}?key={{ $order_key }}';
            const maxAttempts = 60;
            let attempts = 0;

            function checkPaymentStatus() {
                attempts++;

                fetch(checkUrl, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (response.redirected) {
                        window.location.href = response.url;
                        return;
                    }

                    if (response.status === 200) {
                        return response.text();
                    }

                    throw new Error('Unexpected response');
                })
                .then(html => {
                    if (attempts < maxAttempts) {
                        setTimeout(checkPaymentStatus, 2000);
                    } else {
                        window.location.reload();
                    }
                })
                .catch(error => {
                    console.error('Payment check failed:', error);
                    if (attempts < maxAttempts) {
                        setTimeout(checkPaymentStatus, 3000);
                    }
                });
            }

            setTimeout(checkPaymentStatus, 2000);
        })();
    </script>
    @endpush
</x-layouts.app>
