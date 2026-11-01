@props(['advertisement', 'src' => null])

{{--
    A video advertisement, showing only the part chosen for it.

    Without a chosen frame the clip fills the banner as before.
    With one, the banner becomes a window and the clip is sized
    and moved behind it, so exactly that part shows. The file
    itself is never cut.
--}}
@php
    $frame = $advertisement->videoFrame();
    $source = $src ?? $advertisement->mediaUrl();
@endphp

@if($frame)
    <div
        {{ $attributes->only('class') }}
        style="position:relative;overflow:hidden;"
        data-ad-video-frame
    >
        <video
            src="{{ $source }}"
            {{ $attributes->except('class') }}
            style="position:absolute;display:block;max-width:none;object-fit:fill;width:{{ $frame['width'] }}%;height:{{ $frame['height'] }}%;left:{{ $frame['left'] }}%;top:{{ $frame['top'] }}%;"
        ></video>
    </div>
@else
    <video
        src="{{ $source }}"
        {{ $attributes }}
    ></video>
@endif
