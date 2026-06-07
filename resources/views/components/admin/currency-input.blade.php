@props([
    'name',
    'value' => 0,
    'required' => false,
])

@php
    $initialValue = preg_replace('/\D/', '', (string) $value);
@endphp

<div
    x-data="{
        raw: @js($initialValue === '' ? '0' : $initialValue),
        display: '',
        init() {
            this.display = this.format(this.raw);
        },
        normalize(value) {
            return String(value ?? '').replace(/\D/g, '');
        },
        format(value) {
            const clean = this.normalize(value);

            if (clean === '') {
                return '';
            }

            return new Intl.NumberFormat('id-ID').format(Number(clean));
        },
        sync(value) {
            this.raw = this.normalize(value);
            this.display = this.format(this.raw);
        },
        fallbackZero() {
            if (this.display === '') {
                this.raw = '0';
                this.display = '0';
            }
        },
    }"
>
    <input type="hidden" name="{{ $name }}" :value="raw || '0'">
    <input
        type="text"
        inputmode="numeric"
        x-model="display"
        x-on:input="sync($event.target.value)"
        x-on:blur="fallbackZero()"
        @if($required) required @endif
        {{ $attributes->merge(['class' => 'mt-1 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500']) }}
    >
</div>
