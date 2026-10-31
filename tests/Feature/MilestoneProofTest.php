<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
    $this->student = User::factory()->create(['role' => 'student']);
    $this->milestone = $this->student->milestones()->create([
        'title' => 'Complete a course',
        'category' => 'skill',
        'proof_file_path' => 'proofs/certificate.pdf',
    ]);
    Storage::disk('local')->put('proofs/certificate.pdf', "%PDF-1.4\nProof of completion");
});

test('saved proof can be viewed and downloaded before and after completion', function (bool $completed) {
    $this->milestone->update(['is_completed' => $completed]);

    $this->actingAs($this->student)->get(route('student.milestones'))
        ->assertOk()
        ->assertSee('href="'.route('student.milestones.proof', $this->milestone).'"', false)
        ->assertSee('href="'.route('student.milestones.proof', ['milestone' => $this->milestone, 'download' => 1]).'"', false)
        ->assertSeeText('View proof');
})->with([false, true]);

test('PDF proof opens inline and can still be explicitly downloaded', function () {
    $this->actingAs($this->student)
        ->get(route('student.milestones.proof', $this->milestone))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf')
        ->assertHeader('Content-Disposition', 'inline; filename=certificate.pdf');

    $this->get(route('student.milestones.proof', ['milestone' => $this->milestone, 'download' => 1]))
        ->assertDownload('certificate.pdf');
});

test('image proof opens inline', function () {
    $file = UploadedFile::fake()->image('certificate.png');
    $path = $file->store('proofs', 'local');
    $this->milestone->update(['proof_file_path' => $path]);

    $response = $this->actingAs($this->student)
        ->get(route('student.milestones.proof', $this->milestone))
        ->assertOk()
        ->assertHeader('Content-Type', 'image/png');

    expect($response->headers->get('Content-Disposition'))->toStartWith('inline;');
});

test('files without a browser preview remain downloads', function () {
    Storage::disk('local')->put('proofs/certificate.doc', 'Document content');
    $this->milestone->update(['proof_file_path' => 'proofs/certificate.doc']);

    $this->actingAs($this->student)
        ->get(route('student.milestones.proof', $this->milestone))
        ->assertDownload('certificate.doc');
});

test('another student cannot view or download a proof', function (bool $download) {
    $this->actingAs(User::factory()->create(['role' => 'student']))
        ->get(route('student.milestones.proof', ['milestone' => $this->milestone, 'download' => $download]))
        ->assertForbidden();
})->with([false, true]);

test('missing proof returns not found', function () {
    Storage::disk('local')->delete($this->milestone->proof_file_path);

    $this->actingAs($this->student)
        ->get(route('student.milestones.proof', $this->milestone))
        ->assertNotFound();
});

test('new uploads offer preview controls without requiring completion', function () {
    $this->actingAs($this->student)->get(route('student.milestones'))
        ->assertOk()
        ->assertSee('id="add-proof-file-preview"', false)
        ->assertSee('id="proof-'.$this->milestone->id.'-preview"', false);
});
