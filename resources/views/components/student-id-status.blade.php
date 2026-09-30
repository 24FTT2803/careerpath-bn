@props([
    'student',
    'actions' => false,
    'showId' => true,
])

@php
    $hasId = filled($student->student_id);
    $isVerified = $student->hasVerifiedStudentId();
@endphp

<span style="display:inline-flex;align-items:center;gap:8px;flex-wrap:wrap;">
    @if($showId)
        <span>{{ $student->student_id ?? 'Not set' }}</span>
    @endif

    @if($hasId)
        @if($isVerified)
            <span
                title="Verified {{ $student->student_id_verified_at->format('j M Y') }}"
                style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:#dcfce7;color:#166534;"
            >
                <i class="fas fa-check-circle"></i> Verified
            </span>
        @else
            <span
                title="Waiting for an admin or lecturer to confirm this ID"
                style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:600;background:#fef3c7;color:#92400e;"
            >
                <i class="fas fa-clock"></i> Pending
            </span>
        @endif
    @endif

    @if($actions && $hasId)
        @if($isVerified)
            <form
                method="POST"
                action="{{ route('staff.student-id.revoke', $student) }}"
                style="display:inline;margin:0;"
                data-student-id-revoke
            >
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    style="background:none;border:0;padding:0;font-size:12px;color:#6b7280;text-decoration:underline;cursor:pointer;"
                >
                    Undo
                </button>
            </form>

            @once
                <script>
                    document.addEventListener('submit', function (event) {
                        const form = event.target.closest('form[data-student-id-revoke]');

                        if (! form) {
                            return;
                        }

                        if (form.dataset.approved === '1') {
                            delete form.dataset.approved;

                            return;
                        }

                        event.preventDefault();

                        const approve = function () {
                            form.dataset.approved = '1';
                            form.requestSubmit();
                        };

                        const message = 'Remove verification for this Student ID? The student will be able to change it again.';

                        if (typeof window.showConfirmModal !== 'function') {
                            if (window.confirm(message)) {
                                approve();
                            }

                            return;
                        }

                        window.showConfirmModal({
                            title: 'Remove Verification',
                            message: message,
                            confirmText: 'Yes, Remove',
                            cancelText: 'Cancel',
                            type: 'warning',
                            onConfirm: approve,
                        });
                    });
                </script>
            @endonce

        @else
            <form
                method="POST"
                action="{{ route('staff.student-id.verify', $student) }}"
                style="display:inline;margin:0;"
            >
                @csrf
                <button
                    type="submit"
                    style="display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:6px;border:0;font-size:12px;font-weight:600;background:#166534;color:#fff;cursor:pointer;"
                >
                    <i class="fas fa-check"></i> Verify
                </button>
            </form>
        @endif
    @endif
</span>