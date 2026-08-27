@props(['items' => []])

@php
    $breadcrumbs = collect($items)
        ->filter(fn ($item) => filled($item['label'] ?? null))
        ->values();
@endphp

@if($breadcrumbs->isNotEmpty())
    <nav class="teacher-breadcrumbs" aria-label="Migas de pan">
        <ol>
            @foreach($breadcrumbs as $item)
                @php($isCurrent = $loop->last)
                <li class="teacher-breadcrumbs-item" @if($isCurrent) aria-current="page" @endif>
                    @if(!$isCurrent && filled($item['url'] ?? null))
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    @else
                        <span>{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
