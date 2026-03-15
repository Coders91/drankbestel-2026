<div>
    <form method="post" style="margin-top: 20px;">
        @php wp_nonce_field('save_search_attributes') @endphp

        <table class="form-table">
            <tr>
                <th scope="row">
                    {{ __('Attribute taxonomieën', 'sage') }}
                </th>
                <td>
                    <fieldset>
                        <p class="description" style="margin-bottom: 12px;">
                            {{ __('Selecteer welke WooCommerce attributen meegewogen worden in de zoekrangschikking. Producten met een overeenkomend attribuut worden hoger gerangschikt.', 'sage') }}
                        </p>

                        @forelse($attributeTaxonomies as $taxonomy)
                            <label style="display: block; margin-bottom: 6px;">
                                <input
                                    type="checkbox"
                                    name="attribute_taxonomies[]"
                                    value="{{ $taxonomy->attribute_name }}"
                                    @checked(in_array($taxonomy->attribute_name, $selectedAttributes))
                                >
                                {{ $taxonomy->attribute_label }}
                                <code style="font-size: 11px; color: #666;">pa_{{ $taxonomy->attribute_name }}</code>
                            </label>
                        @empty
                            <p class="description">
                                {{ __('Geen WooCommerce attributen gevonden.', 'sage') }}
                            </p>
                        @endforelse
                    </fieldset>
                </td>
            </tr>
        </table>

        @php submit_button(__('Attributen opslaan', 'sage')) @endphp
    </form>

    @if(count($selectedAttributes) > 0)
        <div class="card" style="padding: 20px; margin-top: 20px; max-width: 800px;">
            <h2 style="margin-top: 0;">{{ __('Actieve attributen', 'sage') }}</h2>
            <p class="description" style="margin-top: -10px;">
                {{ __('Deze attributen worden gebruikt als rankingfactor bij het zoeken naar producten.', 'sage') }}
            </p>
            <ul style="margin: 0;">
                @foreach($selectedAttributes as $attr)
                    <li><code>pa_{{ $attr }}</code> — {{ wc_attribute_label('pa_' . $attr) }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
