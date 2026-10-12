<?php

namespace App\Services\Biicf;

use App\Services\Biicf\Pdf\BookReader;
use App\Services\Biicf\Pdf\PageLayout;
use RuntimeException;
use Smalot\PdfParser\Config;
use Smalot\PdfParser\Parser;
use Throwable;

/**
 * Reads an edition from the reference book itself.
 *
 * Its own work is small: open the PDF and hand over each page's
 * text with the position it is printed at. Everything that is
 * actually difficult about reading this book is geometry — which
 * column a line belongs to, whether it opens a bullet or
 * continues one, which level a figure in a table is counted
 * against — and all of that lives in {@see BookReader}, where it
 * can be tested without a PDF anywhere near it.
 *
 * The split is deliberate. A reader tuned to one printing is not
 * guaranteed the next, and the library that turns pages into
 * text is a dependency like any other. Keeping the two apart
 * means either can be replaced without the other.
 *
 * Nothing it reads reaches a student. An edition is staged as a
 * list of differences, every one of which an administrator
 * decides on before any of it is applied.
 */
class PdfEditionExtractor implements EditionExtractor
{
    public function __construct(
        private readonly BookReader $reader = new BookReader,
    ) {}

    public function extract(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("There is no file at {$path}.");
        }

        if (! class_exists(Parser::class)) {
            throw new RuntimeException(
                'The PDF reader is not installed, so the book cannot be read '
                .'here. Run "composer require smalot/pdfparser", or upload an '
                .'edition already extracted to JSON.'
            );
        }

        return $this->reader->read($this->pages($path));
    }

    public function extensions(): array
    {
        return ['pdf'];
    }

    public function describe(): string
    {
        return 'The framework as AITI publishes it, as a PDF.';
    }

    /**
     * A reader set up to keep as little as possible.
     *
     * The book is a designed document: three hundred pages of
     * photographs, panels and rules around the text. By default
     * every one of those images is decoded and held in memory
     * along with it, and the whole thing runs out of room before
     * it reaches the end — which is what happened the first time
     * the real book was read.
     *
     * None of it is wanted. The only thing being read here is
     * where the words are.
     */
    private function parser(): Parser
    {
        if (! class_exists(Config::class)) {
            return new Parser;
        }

        $config = new Config;

        if (method_exists($config, 'setRetainImageContent')) {
            $config->setRetainImageContent(false);
        }

        return new Parser([], $config);
    }

    /**
     * Every page of the book, as positioned text.
     *
     * @return array<int, PageLayout>
     */
    private function pages(string $path): array
    {
        try {
            $document = $this->parser()->parseFile($path);
        } catch (Throwable $e) {
            throw new RuntimeException(
                'That file could not be opened as a PDF: '.$e->getMessage()
            );
        }

        $pages = [];

        foreach ($document->getPages() as $number => $page) {
            $runs = [];

            foreach ($this->positionsOn($page) as [$matrix, $text]) {
                if (trim((string) $text) === '') {
                    continue;
                }

                $runs[] = [
                    't' => (string) $text,
                    'x' => (float) ($matrix[4] ?? 0),
                    /*
                     * A PDF counts upward from the foot of the
                     * page and the reading counts downward from
                     * the head, so the sign is turned over. The
                     * height is not needed and not asked for:
                     * nothing downstream reads a position on its
                     * own, only one against another.
                     */
                    'y' => -1 * (float) ($matrix[5] ?? 0),
                ];
            }

            $pages[] = PageLayout::from($runs, $number);
        }

        if ($pages === []) {
            throw new RuntimeException('That PDF has no pages in it.');
        }

        return $pages;
    }

    /**
     * @return array<int, array{0: array<int, float>, 1: string}>
     */
    private function positionsOn(object $page): array
    {
        if (! method_exists($page, 'getDataTm')) {
            throw new RuntimeException(
                'The installed PDF reader cannot report where text sits on the '
                .'page, and this book cannot be read without that.'
            );
        }

        try {
            /** @var array<int, array{0: array<int, float>, 1: string}> $data */
            $data = $page->getDataTm();
        } catch (Throwable $e) {
            throw new RuntimeException(
                'A page of that PDF could not be read: '.$e->getMessage()
            );
        }

        return $data;
    }
}
