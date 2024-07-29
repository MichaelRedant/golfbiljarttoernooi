@if($sponsors->isEmpty())
    <p class="text-center">Geen sponsors op dit moment.</p>
@else
<div id="sponsorsCarousel" class="carousel slide pt-4" data-bs-ride="carousel" data-bs-interval="8000">
     <div class="carousel-inner">
        @foreach($sponsors->chunk(3) as $chunk)
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <div class="row">
                    @foreach($chunk as $sponsor)
                        <div class="col">
                            <a href="{{ $sponsor->url }}" target="_blank">
                                <img src="{{ asset($sponsor->image_path) }}" class="sponsor-image" alt="{{ $sponsor->name }}">
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

@endif
