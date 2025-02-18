<?php

declare(strict_types=1);

namespace KerrialNewham\ComposerJsonParser\ComposerJsonFinder;

final class ComposerJsonFinder
{
    private function findComposerJson(string $cwd): null|string
    {
        while ($cwd !== DIRECTORY_SEPARATOR) {
            $composerJsonPath = $cwd . DIRECTORY_SEPARATOR . 'composer.json';

            if (! file_exists($composerJsonPath)) {
                $cwd = dirname($cwd);
                continue;
            }

            return $composerJsonPath;
        }

        return null;
    }

    public function getComposerJsonData(): array
    {
        $composerJsonPath = $this->findComposerJson(getcwd());

        if (empty($composerJsonPath)) {
            return [];
        }

        $composerJsonContents = file_get_contents($composerJsonPath);
        $composerJsonData = json_decode($composerJsonContents, true);
        return $composerJsonData;
    }
}
