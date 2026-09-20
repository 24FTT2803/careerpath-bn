@props(['advertisement'])

@php
    $mediaUrl = $advertisement->mediaUrl();
    $label = $advertisement->alt_text ?: $advertisement->title;
@endphp

@if($advertisement->isNetworkEmbed())
            {{--
        Supplied by an advertising network. Framed rather
        than inlined so a third party cannot reach into
        the page around it.
            --}}
    <iframe
        src="{{ $mediaUrl }}"
        title="{{ $label }}"
        loading="lazy"
        referrerpolicy="no-referrer"
        sandbox="allow-scripts allow-popups allow-popups-to-escape-sandbox"
        class="ad-slot-frame"
            ></iframe>

@elseif($advertisement->isVideo())
    <video
        src="{{ $mediaUrl }}"
        class="ad-slot-media"
        muted
        loop
        autoplay
        playsinline
        aria-label="{{ $label }}"
            ></video>

@elseif($mediaUrl)
    @if($advertisement->click_url)
        <a
            href="{{ $advertisement->click_url }}"
            target="_blank"
            rel="noopener sponsored"
        >
            <img
                src="{{ $mediaUrl }}"
                alt="{{ $label }}"
                class="ad-slot-media"
                loading="lazy"
            >
        </a>
    @else
        <img
            src="{{ $mediaUrl }}"
            alt="{{ $label }}"
            class="ad-slot-media"
            loading="lazy"
        >
    @endif

@elseif($advertisement->click_url)
    <a
        href="{{ $advertisement->click_url }}"
        target="_blank"
        rel="noopener sponsored"
        class="ad-slot-text"
            >
        {{ $advertisement->title }}
    </a>
@endif
