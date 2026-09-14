@props([
    'selected' => null,
    'name' => 'programme',
    'placeholder' => 'Select your programme',
])

@php
    $programmes = app(
        App\Services\Business\ProgrammeEnrolmentService::class
    )->options();
@endphp

<select name="{{ $name }}" {{ $attributes }}>
    <option value="">{{ $placeholder }}</option>

    @foreach($programmes as $programme)
        <option
            value="{{ $programme->name }}"
            @selected(old($name, $selected) === $programme->name)
        >
            @if($programme->code)
                {{ $programme->code }} — {{ $programme->name }}
            @else
                {{ $programme->name }}
            @endif
        </option>
    @endforeach
</select>
