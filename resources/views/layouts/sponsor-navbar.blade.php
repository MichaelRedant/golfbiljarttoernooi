@if($sponsors->isEmpty())
    <p class="text-center">Geen sponsors op dit moment.</p>
@else
<div id="sponsorsCarousel" class="carousel slide pt-4" data-bs-ride="carousel" data-bs-interval="4000">
    <div class="carousel-inner">
        @foreach($sponsors as $sponsor)
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <div class="d-flex justify-content-center">
                    <a href="{{ $sponsor->url }}" target="_blank">
                        <img src="{{ asset($sponsor->image_path) }}" class="sponsor-image" alt="{{ $sponsor->name }}">
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endif
