<?php

namespace App\Support;

use App\Models\StudentCertification;
use App\Models\StudentMilestone;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

/**
 * Gathers a student's uploaded evidence (certificates and
 * milestone proofs) for the PDF report.
 *
 * dompdf cannot embed PDF or Word files, so only JPG/PNG are
 * drawn into the report. Other file types are listed by name so
 * the reader knows something was uploaded and can open it on
 * the platform.
 */
class EvidenceFiles
{
    private const MAX_WIDTH = 1100;

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png'];

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function for(User $student): array
    {
        $items = [];

        $certifications = StudentCertification::where('user_id', $student->id)
            ->whereNotNull('certificate_file_path')
            ->orderBy('issue_date')
            ->get();

        foreach ($certifications as $cert) {
            $items[] = self::item(
                'Certification',
                $cert->certification_name,
                $cert->issuing_organization,
                $cert->certificate_file_path,
                $cert->certificate_original_name
            );
        }

        $milestones = StudentMilestone::where('user_id', $student->id)
            ->whereNotNull('proof_file_path')
            ->orderBy('completed_date')
            ->get();

        foreach ($milestones as $milestone) {
            $items[] = self::item(
                'Milestone proof',
                $milestone->title,
                $milestone->is_completed ? 'Completed' : 'In progress',
                $milestone->proof_file_path,
                basename($milestone->proof_file_path)
            );
        }

        return $items;
    }

    /**
     * @return array<string, mixed>
     */
    private static function item(
        string $kind,
        ?string $title,
        ?string $detail,
        string $path,
        ?string $name
    ): array {
        $disk = Storage::disk('local');
        $exists = $disk->exists($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return [
            'kind' => $kind,
            'title' => $title,
            'detail' => $detail,
            'name' => $name ?: basename($path),
            'extension' => $extension,
            'missing' => ! $exists,
            'image' => $exists && in_array($extension, self::IMAGE_EXTENSIONS, true)
                ? self::dataUri($disk->path($path), $extension)
                : null,
        ];
    }

    /**
     * Shrink the image and return it as a data URI, so a phone
     * photo of several MB does not balloon the PDF.
     */
    private static function dataUri(string $fullPath, string $extension): ?string
    {
        $raw = @file_get_contents($fullPath);

        if ($raw === false) {
            return null;
        }

        $fallbackMime = $extension === 'png' ? 'image/png' : 'image/jpeg';

        if (! function_exists('imagecreatefromstring')) {
            return 'data:'.$fallbackMime.';base64,'.base64_encode($raw);
        }

        $source = @imagecreatefromstring($raw);

        if ($source === false) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);

        if ($width > self::MAX_WIDTH) {
            $newHeight = (int) round($height * (self::MAX_WIDTH / $width));
            $scaled = imagescale($source, self::MAX_WIDTH, $newHeight);

            if ($scaled !== false) {
                imagedestroy($source);
                $source = $scaled;
            }
        }

        // Flatten transparency onto white, then store as JPEG.
        $canvas = imagecreatetruecolor(imagesx($source), imagesy($source));
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));

        ob_start();
        imagejpeg($canvas, null, 80);
        $jpeg = ob_get_clean();

        imagedestroy($source);
        imagedestroy($canvas);

        return $jpeg ? 'data:image/jpeg;base64,'.base64_encode($jpeg) : null;
    }
}
