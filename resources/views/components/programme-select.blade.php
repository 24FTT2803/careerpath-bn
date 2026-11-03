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

    // Order the visible programme list by its existing database school links.
    // No database changes, code changes or changes to submitted values.
    $schoolLabels = [
        'SICT' => 'School of Information and Communication Technology (SICT)',
        'SBS' => 'School of Business (SBS)',
        'SSE' => 'School of Science and Engineering (SSE)',
        'SHS' => 'School of Health Sciences (SHS)',
        'SPC' => 'School of Petrochemical (SPC)',
    ];

    // Eager load the existing parents to avoid one database query per programme.
    $programmes->load('parents');
    $groupedProgrammes = array_fill_keys(array_keys($schoolLabels), []);
    $groupedProgrammes['OTHER'] = [];

    foreach ($programmes as $programme) {
        $schoolCode = $programme->parents->first(
            fn ($parent) => array_key_exists($parent->code, $schoolLabels)
        )?->code ?? 'OTHER';

        // Never hide an active programme because its school is not assigned.
        $groupedProgrammes[$schoolCode][] = $programme;
    }
@endphp

<select name="{{ $name }}" {{ $attributes }}>
    <option value="">{{ $placeholder }}</option>

    @foreach($groupedProgrammes as $schoolCode => $schoolProgrammes)
        @if(count($schoolProgrammes) > 0)
            <optgroup label="{{ $schoolLabels[$schoolCode] ?? 'Other programmes' }}">
                @foreach($schoolProgrammes as $programme)
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
            </optgroup>
        @endif
    @endforeach
</select>
