<div class="wrap">
    <h1>{{ __('Zoekanalyse', 'sage') }}</h1>
    <p class="description">{{ __('Analyse van zoekopdrachten van de afgelopen 30 dagen', 'sage') }}</p>

    {{-- Stats Cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 20px 0;">
        <div class="card" style="padding: 20px; margin: 0;">
            <h3 style="margin: 0 0 10px 0; color: #666;">{{ __('Totaal zoekopdrachten', 'sage') }}</h3>
            <p style="font-size: 32px; font-weight: bold; margin: 0; color: #2271b1;">
                {{ number_format($stats['total_searches']) }}
            </p>
        </div>

        <div class="card" style="padding: 20px; margin: 0;">
            <h3 style="margin: 0 0 10px 0; color: #666;">{{ __('Unieke zoektermen', 'sage') }}</h3>
            <p style="font-size: 32px; font-weight: bold; margin: 0; color: #2271b1;">
                {{ number_format($stats['unique_queries']) }}
            </p>
        </div>

        <div class="card" style="padding: 20px; margin: 0;">
            <h3 style="margin: 0 0 10px 0; color: #666;">{{ __('Gem. resultaten', 'sage') }}</h3>
            <p style="font-size: 32px; font-weight: bold; margin: 0; color: #2271b1;">
                {{ $stats['avg_results'] }}
            </p>
        </div>

        <div class="card" style="padding: 20px; margin: 0;">
            <h3 style="margin: 0 0 10px 0; color: #666;">{{ __('Zonder resultaten', 'sage') }}</h3>
            <p style="font-size: 32px; font-weight: bold; margin: 0; color: {{ $stats['zero_result_rate'] > 20 ? '#d63638' : '#2271b1' }};">
                {{ $stats['zero_result_rate'] }}%
            </p>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                {{ number_format($stats['zero_result_searches']) }} {{ __('zoekopdrachten', 'sage') }}
            </p>
        </div>
    </div>

    {{-- Chart --}}
    <div class="card" style="padding: 20px; margin: 20px 0;">
        <h2 style="margin-top: 0;">{{ __('Zoekopdrachten per dag', 'sage') }}</h2>
        <canvas id="searchChart" height="100"></canvas>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        {{-- Popular Searches --}}
        <div class="card" style="padding: 20px; margin: 0;">
            <h2 style="margin-top: 0;">{{ __('Populaire zoektermen', 'sage') }}</h2>
            @if(count($popularSearches) > 0)
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>{{ __('Zoekterm', 'sage') }}</th>
                            <th style="text-align: right;">{{ __('Aantal', 'sage') }}</th>
                            <th style="text-align: right;">{{ __('Gem. resultaten', 'sage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($popularSearches as $search)
                            <tr>
                                <td><strong>{{ $search->query }}</strong></td>
                                <td style="text-align: right;">{{ number_format($search->search_count) }}</td>
                                <td style="text-align: right;">{{ round($search->avg_results) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="description">{{ __('Nog geen zoekgegevens beschikbaar.', 'sage') }}</p>
            @endif
        </div>

        {{-- Zero Result Searches --}}
        <div class="card" style="padding: 20px; margin: 0;">
            <h2 style="margin-top: 0;">{{ __('Zoektermen zonder resultaten', 'sage') }}</h2>
            <p class="description" style="margin-top: -10px;">
                {{ __('Overweeg synoniemen of nieuwe producten toe te voegen voor deze zoektermen.', 'sage') }}
            </p>
            @if(count($zeroResultSearches) > 0)
                <table class="widefat striped">
                    <thead>
                        <tr>
                            <th>{{ __('Zoekterm', 'sage') }}</th>
                            <th style="text-align: right;">{{ __('Aantal', 'sage') }}</th>
                            <th>{{ __('Laatst gezocht', 'sage') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zeroResultSearches as $search)
                            <tr>
                                <td><strong style="color: #d63638;">{{ $search->query }}</strong></td>
                                <td style="text-align: right;">{{ number_format($search->search_count) }}</td>
                                <td>{{ date('d-m-Y H:i', strtotime($search->last_searched)) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="description">{{ __('Geen zoektermen zonder resultaten gevonden.', 'sage') }}</p>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('searchChart').getContext('2d');
    const chartData = @json($chartData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData.map(d => {
                const date = new Date(d.date);
                return date.toLocaleDateString('nl-NL', { day: 'numeric', month: 'short' });
            }),
            datasets: [
                {
                    label: '{{ __("Zoekopdrachten", "sage") }}',
                    data: chartData.map(d => d.search_count),
                    borderColor: '#2271b1',
                    backgroundColor: 'rgba(34, 113, 177, 0.1)',
                    fill: true,
                    tension: 0.3
                },
                {
                    label: '{{ __("Zonder resultaten", "sage") }}',
                    data: chartData.map(d => d.zero_results),
                    borderColor: '#d63638',
                    backgroundColor: 'rgba(214, 54, 56, 0.1)',
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
});
</script>
