<?php

declare(strict_types=1);

namespace KerrialNewham\ComposerJsonParser\ComposerJsonFinder;

final class ComposerJsonFinder
{
    public function getComposerJsonData(string $path): array
    {
        $composerJsonContents = file_get_contents($path);
        $composerJsonData = json_decode($composerJsonContents, true);
        return $composerJsonData;
    }
}
