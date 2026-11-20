<?php
// SPDX-FileCopyrightText: 2026 LibreCode coop and contributors
// SPDX-License-Identifier: AGPL-3.0-or-later

declare(strict_types=1);

namespace Modules\Theme\Domain;

final class LoginSlides
{
    public const LIMIT = 6;

    /**
     * Plan a form update without trusting any client-supplied existing image
     * path. Persisted images are reused by original position; only new uploads
     * may replace them. Omitted existing rows remain untouched.
     *
     * @return array<int, array{title: string, caption: string, image: string, upload_index?: int}>
     */
    public static function reconcile(
        array $existing,
        array $titles,
        array $captions,
        array $deleted,
        array $uploads,
    ): array {
        $existing = array_values($existing);
        $indices = array_unique(array_merge(
            array_keys($existing),
            array_keys($titles),
            array_keys($captions),
            array_keys($deleted),
            array_keys($uploads),
        ));

        foreach ($indices as $index) {
            if (! is_int($index) || $index < 0 || $index > 99) {
                throw new \InvalidArgumentException('Invalid slide index.');
            }
        }
        sort($indices, SORT_NUMERIC);

        $result = [];
        foreach ($indices as $index) {
            if (filter_var($deleted[$index] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }

            $old = $existing[$index] ?? [];
            $title = trim((string) ($titles[$index] ?? $old['title'] ?? ''));
            $caption = trim((string) ($captions[$index] ?? $old['caption'] ?? ''));
            $image = (string) ($old['image'] ?? '');
            $hasUpload = ! empty($uploads[$index]);

            // An empty, freshly added row can be ignored. An existing slide
            // cannot disappear because the client omitted a title/file.
            if ($old === [] && $title === '' && $caption === '' && ! $hasUpload) {
                continue;
            }
            if ($title === '' || ($image === '' && ! $hasUpload)) {
                throw new \InvalidArgumentException('Every active slide needs a title and an image.');
            }

            $slide = ['title' => $title, 'caption' => $caption, 'image' => $image];
            if ($hasUpload) {
                $slide['upload_index'] = $index;
            }
            $result[] = $slide;
        }

        if (count($result) > self::LIMIT) {
            throw new \InvalidArgumentException('Too many login slides.');
        }

        return $result;
    }
}
