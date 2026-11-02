@php
    $teamGroups = [
        'Project Leader' => [
            ['name' => 'Siti Khairunnisa binti Abdullah [24FTT1870] ', 'role' => 'Project Leader, CareerPath BN', 'photo' => 'khai.jpg'],
        ],
        'Application Development Group' => [
            ['name' => 'Justine Chai Wei Sen [24FTT2803]', 'role' => 'Frontend Developer & System Interface Developer', 'photo' => 'justine.jpg'],
            ['name' => 'Syafiq Hafizuddin bin Alimin [24FTT1802]', 'role' => 'AI & System Integration Developer', 'photo' => 'syafiq.jpg'],
            ['name' => 'Mohamad Nazmin bin Mohamad Dahrin [24FTT1804]', 'role' => 'Backend & Feature Developer', 'photo' => 'nazmin.jpg'],
        ],
        'Data Analytics Group' => [
            ['name' => 'Faizatul Nasfazeerah binti Mohammad Nadi Nabillah [24FTT1868]', 'role' => 'Database Engineer, UI/UX & Brand Designer', 'photo' => 'faii.jpg'],
            ['name' => 'Angellyna Azriel binti Birosleh [24FTT1842]', 'role' => 'Database Engineer & Data Architect', 'photo' => 'angel.jpg'],
        ],
    ];
@endphp

@foreach ($teamGroups as $groupTitle => $members)
    <div class="team-group">
        <h2 class="team-group-title">{{ $groupTitle }}</h2>

        <div class="team-list reveal-stagger">
            @foreach ($members as $member)
                <div class="team-member">
                    <img
                        src="{{ asset('images/team/' . $member['photo']) }}"
                        alt="{{ $member['name'] }}"
                        loading="lazy"
                        class="team-photo"
                    >
                    <div>
                        <p class="team-name">{{ $member['name'] }}</p>
                        <p class="team-role">
                            {{ $member['role'] }}<br>
                            Politeknik Brunei
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach
