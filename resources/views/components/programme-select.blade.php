@props([
    'selected' => null,
    'selectedGroupId' => null,
    'name' => 'programme',
    'placeholder' => 'Select your programme',
])

@php
    $programmes = app(
        App\Services\Business\ProgrammeEnrolmentService::class
    )->options();

    /*
     * The key decides what is selected wherever it is recorded,
     * so a renamed programme still shows as the student's own.
     * The stored name is only a fallback for accounts that
     * predate the key.
     */
    $chosenId = old($name . '_group_id', $selectedGroupId);
    $chosenName = old($name, $selected);
@endphp

<select name="{{ $name }}" {{ $attributes }}>
    <option value="">{{ $placeholder }}</option>

    @foreach($programmes as $programme)
        @php
            $isChosen = $chosenId !== null
                ? (int) $chosenId === $programme->id
                : $chosenName === $programme->name;
        @endphp

        <option
            value="{{ $programme->name }}"
            @selected($isChosen)
        >
            @if($programme->code)
                {{ $programme->code }} — {{ $programme->name }}
            @else
                {{ $programme->name }}
            @endif
        </option>
    @endforeach
</select>
