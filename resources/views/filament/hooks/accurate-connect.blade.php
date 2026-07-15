<x-filament::link
    :href="route('accurate.connect')"
    color="danger"
    icon="heroicon-o-link"
    x-on:click.prevent="window.location = '{{ route('accurate.connect') }}'"
>
    <span class="hidden sm:inline">Connect Accurate</span>
</x-filament::link>



