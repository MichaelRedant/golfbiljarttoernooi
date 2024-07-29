@if($sponsors->isEmpty())
    <p class="text-center">No sponsors available at the moment.</p>
@else
<div id="sponsorsCarousel" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">
        @forelse($sponsors->chunk(3) as $chunk)
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <div class="d-flex justify-content-center">
                    @foreach($chunk as $sponsor)
                        <div class="p-2">
                            <img src="{{ asset($sponsor->image_path) }}" class="sponsor-image" alt="{{ $sponsor->name }}">
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="carousel-item active">
                <div class="d-flex justify-content-center">
                    <p>Geen sponsors beschikbaar</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

@endif
