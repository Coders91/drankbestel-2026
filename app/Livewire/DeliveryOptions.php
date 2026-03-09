<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Attributes\Modelable;
use Livewire\Attributes\Reactive;
use Livewire\Component;

class DeliveryOptions extends Component
{
    const SESSION_KEY = 'deliveryOptions';

    #[Modelable]
    public ?array $deliverySelection = null;

    #[Reactive]
    public string $postalCode = '';

    #[Reactive]
    public string $houseNumber = '';

    #[Reactive]
    public ?string $houseNumberSuffix = null;

    public array $settings = [
        'delivery_days_window' => 10,
        'allow_monday_delivery' => false,
        'allow_saturday_delivery' => true,
        'allow_morning_delivery' => false,
        'allow_evening_delivery' => false,
        'allow_pickup_locations' => false,
        'cutoff_time' => '17:00',
        'carrier' => 'postnl',
    ];

    public function mount(): void
    {
        $this->loadSelectionFromSession();
    }

    protected function loadSelectionFromSession(): void
    {
        if (function_exists('WC') && WC()->session) {
            $data = WC()->session->get(self::SESSION_KEY, []);
            $this->deliverySelection ??= $data['delivery_selection'] ?? null;
        }
    }

    /**
     * Called from JavaScript when user selects an option
     */
    public function updateSelection(array $selection): void
    {
        $this->deliverySelection = [
            'isPickup' => false,
            'carrier' => $this->settings['carrier'] ?? 'postnl',
            'package_type' => 'package',
            'deliveryType' => 'standard',
            'shipmentOptions' => ['same_day_delivery' => false],
            'date' => $selection['date'],
            'start' => $selection['start'] ?? null,
            'end' => $selection['end'] ?? null,
        ];

        if (function_exists('WC') && WC()->session) {
            WC()->session->set(self::SESSION_KEY, [
                'delivery_selection' => $this->deliverySelection,
            ]);
        }

        $this->dispatch('delivery-option-selected');
    }

    /**
     * Generate placeholder dates for initial display
     */
    public function getPlaceholderDates(): array
    {
        $window = $this->settings['delivery_days_window'];
        $allowMonday = $this->settings['allow_monday_delivery'];
        $allowSaturday = $this->settings['allow_saturday_delivery'];
        $cutoffTime = Carbon::createFromTimeString($this->settings['cutoff_time']);

        $options = [];
        $date = now();

        if (now()->gt($cutoffTime)) {
            $date->addDay();
        }

        for ($i = 0; $i < 14; $i++) {
            if (count($options) >= $window) {
                break;
            }

            $date->addDay();

            if ($date->isSunday()) continue;
            if ($date->isMonday() && !$allowMonday) continue;
            if ($date->isSaturday() && !$allowSaturday) continue;

            $daysDiff = now()->startOfDay()->diffInDays($date->copy()->startOfDay());

            $displayDate = match ($daysDiff) {
                0 => 'vandaag',
                1 => 'morgen (' . $date->locale('nl')->isoFormat('dddd') . ')',
                2 => 'overmorgen (' . $date->locale('nl')->isoFormat('dddd') . ')',
                default => $date->locale('nl')->isoFormat('dddd'),
            };

            $options[] = [
                'date' => $date->format('Y-m-d 00:00:00'),
                'display_date' => $displayDate,
                'date_string' => $date->locale('nl')->isoFormat('D MMMM'),
                'time_string' => '',
                'start' => null,
                'end' => null,
                'is_placeholder' => true,
            ];
        }

        return $options;
    }

    public function render()
    {
        return view('livewire.delivery-options', [
            'placeholderOptions' => $this->getPlaceholderDates(),
            'initialSelection' => $this->deliverySelection,
        ]);
    }
}
