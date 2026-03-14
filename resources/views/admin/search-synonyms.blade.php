<div>
    <form method="post" style="margin-top: 20px;">
        @php wp_nonce_field('save_search_synonyms') @endphp

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="synonyms">{{ __('Synoniemen', 'sage') }}</label>
                </th>
                <td>
                    <textarea
                        name="synonyms"
                        id="synonyms"
                        rows="15"
                        class="large-text code"
                        placeholder="{{ __('whisky, whiskey, wisky', 'sage') }}"
                    >{{ $synonymsText }}</textarea>
                    <p class="description">
                        {{ __('Voer synoniemen in, gescheiden door een komma. Elke regel is een nieuwe groep synoniemen.', 'sage') }}
                        <br>
                        {{ __('Regels die beginnen met # worden als commentaar beschouwd.', 'sage') }}
                        <br>
                        {{ __('Je kunt ook woordgroepen gebruiken, bijvoorbeeld:', 'sage') }}
                        <code>100cl, 1 liter, een liter</code>
                    </p>
                </td>
            </tr>
        </table>

        @php submit_button(__('Synoniemen opslaan', 'sage')) @endphp
    </form>

    @if(count($groups) > 0)
        <div class="card" style="padding: 20px; margin-top: 20px; max-width: 800px;">
            <h2 style="margin-top: 0;">{{ __('Actieve synoniemen', 'sage') }}</h2>
            <p class="description" style="margin-top: -10px;">
                {{ __('Overzicht van de huidige synoniemgroepen die actief zijn in de zoekmachine.', 'sage') }}
            </p>
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('Synoniemen', 'sage') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($groups as $index => $group)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @foreach($group as $term)
                                    <code>{{ $term }}</code>@if(! $loop->last), @endif
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
