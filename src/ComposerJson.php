<?php

declare(strict_types=1);

namespace KerrialNewham\ComposerJsonParser;

use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use KerrialNewham\ComposerJsonParser\ComposerJsonFinder\ComposerJsonFinder;
use KerrialNewham\ComposerJsonParser\Enum\PackageTypeEnum;
use KerrialNewham\ComposerJsonParser\Exception\ComposerJsonNotFoundException;
use KerrialNewham\ComposerJsonParser\Model\Autoload;
use KerrialNewham\ComposerJsonParser\Model\Composer;
use KerrialNewham\ComposerJsonParser\Model\Package;
use KerrialNewham\ComposerJsonParser\Model\Script;
use KerrialNewham\ComposerJsonParser\VersionParser\VersionParser;

final class ComposerJson
{
    private null|string $filePath = null;

    private readonly Composer $composer;

    private array $composerJsonData;

    private readonly VersionParser $versionParser;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $this->versionParser = new VersionParser();
        $this->composer = new Composer();
    }

    /**
     * @throws ComposerJsonNotFoundException
     */
    public function withComposerJsonPath(?string $path = null): self
    {
        $projectRoot = getcwd();
        $resolvedPath = $path ? realpath($path) : $projectRoot;

        if ($resolvedPath === false) {
            throw new ComposerJsonNotFoundException("Invalid path: {$path}");
        }

        if (is_dir($resolvedPath)) {
            $composerJsonPath = $resolvedPath . DIRECTORY_SEPARATOR . 'composer.json';
        } else {
            $composerJsonPath = $resolvedPath;
        }

        if (! file_exists($composerJsonPath) || ! is_file($composerJsonPath)) {
            throw new ComposerJsonNotFoundException("composer.json not found at: " . $composerJsonPath);
        }

        $this->setFilePath($composerJsonPath);
        $this->composerJsonData = (new ComposerJsonFinder())->getComposerJsonData($composerJsonPath);

        return $this;
    }

    public function getComposer(): Composer
    {
        return $this->composer;
    }

    public function withName(): self
    {
        if (array_key_exists('name', $this->composerJsonData)) {
            $this->composer->setName($this->composerJsonData['name']);
        }
        return $this;
    }

    public function withDescription(): self
    {
        if (array_key_exists('description', $this->composerJsonData)) {
            $this->composer->setDescription($this->composerJsonData['description']);
        }
        return $this;
    }

    public function withType(): self
    {
        if (array_key_exists('type', $this->composerJsonData)) {
            $this->composer->setType($this->composerJsonData['type']);
        }

        return $this;
    }

    public function withVersion(): self
    {
        if (array_key_exists('version', $this->composerJsonData)) {
            $this->composer->setVersion($this->versionParser->parseVersionString($this->composerJsonData['version']));
        }
        return $this;
    }

    public function withMinimumStability(): self
    {
        if (array_key_exists('minimum-stability', $this->composerJsonData)) {
            $this->composer->setMinimumStability($this->composerJsonData['minimum-stability']);
        }
        return $this;
    }

    public function withRequire(): self
    {
        if (array_key_exists('require', $this->composerJsonData)) {
            $this->extractRequirePackages($this->composerJsonData['require']);
        }
        return $this;
    }

    public function withRequireDev(): self
    {
        if (array_key_exists('require-dev', $this->composerJsonData)) {
            $this->extractRequireDevPackages($this->composerJsonData['require-dev']);
        }
        return $this;
    }

    public function withAutoload(): self
    {
        if (array_key_exists('autoload', $this->composerJsonData) &&
            array_key_exists('psr-4', $this->composerJsonData['autoload'])) {
            $this->extractAutoloads($this->composerJsonData['autoload']['psr-4']);
        }
        return $this;
    }

    public function withScripts(): self
    {
        if (array_key_exists('scripts', $this->composerJsonData)) {
            $this->extractScripts($this->composerJsonData['scripts']);
        }
        return $this;
    }

    private function extractRequirePackages(array $composerRequirePackages): void
    {
        foreach ($composerRequirePackages as $name => $version) {
            $package = new Package(
                name: $name,
                type: PackageTypeEnum::REQUIRE,
                packageVersion: $this->versionParser->parseVersionString($version)
            );
            $this->composer->addRequire($package);
        }
    }

    private function extractAutoloads(array $composerAutoload): void
    {
        foreach ($composerAutoload as $namespace => $path) {
            $autoload = new Autoload(namespace: $namespace, path: $path);
            $this->composer->addAutoload($autoload);
        }
    }

    private function extractScripts(array $composerScripts): void
    {
        foreach ($composerScripts as $name => $command) {
            if (is_array($command)) {
                continue;
            }

            $script = new Script(name: $name, command: $command);
            $this->composer->addScript($script);
        }
    }

    private function extractRequireDevPackages(array $composerRequireDevPackages): void
    {
        foreach ($composerRequireDevPackages as $name => $version) {
            $package = new Package(
                name: $name,
                type: PackageTypeEnum::DEVELOPMENT,
                packageVersion: $this->versionParser->parseVersionString($version)
            );
            $this->composer->addDevRequire($package);
        }
    }

    public function addRequire(Package $package): void
    {
        $this->composer->addRequire($package);
        $this->composerJsonData['require'][$package->getName()] = $package->getPackageVersion()->getVersionString();
        $this->save();
    }

    public function addDevAutoload(Autoload $autoload): self
    {
        $this->composer->addDevAutoload($autoload);
        $this->composerJsonData['autoload-dev']['psr-4'][$autoload->getNamespace()] = $autoload->getPath();
        $this->save();
        return $this;
    }

    public function addReplace(Package $replace): self
    {
        $this->composer->addReplace($replace);
        $this->composerJsonData['replace'][$replace->getName()] = $replace->getPackageVersion()->getVersionString();
        $this->save();
        return $this;
    }

    public function addScript(Script $script): self
    {
        $this->composer->addScript(script: $script);
        $this->composerJsonData['scripts'][$script->getName()] = $script->getCommand();
        $this->save();
        return $this;
    }

    public function addConflict(Package $package): self
    {
        $this->composer->addConflict(package: $package);
        $this->composerJsonData['conflict'][$package->getName()] = $package->getPackageVersion()->getVersionString();
        $this->save();
        return $this;
    }

    public function addExtra(mixed $extra): self
    {
        $this->composer->addExtra($extra);
        $this->composerJsonData['extra'] = $extra;
        $this->save();
        return $this;
    }

    public function addRequireDev(Package $package): self
    {
        $this->composer->addDevRequire(package: $package);
        $this->composerJsonData['require-dev'][$package->getName()] = $package->getPackageVersion()->getVersionString();
        $this->save();
        return $this;
    }

    public function removeRequire(Package $package): self
    {
        $this->composer->removeRequire($package);
        unset($this->composerJsonData['require'][$package->getName()]);
        $this->save();
        return $this;
    }

    public function removeConfig(Package $package): self
    {
        $this->composer->removeConfig($package);
        unset($this->composerJsonData['config'][$package->getName()]);
        $this->save();
        return $this;
    }

    public function removeAutoload(Autoload $autoload): self
    {
        $this->composer->removeAutoload($autoload);
        unset($this->composerJsonData['autoload'][$autoload->getNamespace()]);
        $this->save();
        return $this;
    }

    public function removeDevAutoload(Autoload $autoload): self
    {
        $this->composer->removeDevAutoload($autoload);
        unset($this->composerJsonData['autoload-dev'][$autoload->getNamespace()]);
        $this->save();
        return $this;
    }

    public function removeReplace(Package $package): self
    {
        $this->composer->removeReplace($package);
        unset($this->composerJsonData['replace'][$package->getName()]);
        $this->save();
        return $this;
    }

    public function removeScript(Script $script): self
    {
        $this->composer->removeScript($script);
        unset($this->composerJsonData['scripts'][$script->getName()]);
        $this->save();
        return $this;
    }

    public function removeConflict(Package $package): self
    {
        $this->composer->removeConflict($package);
        unset($this->composerJsonData['conflict'][$package->getName()]);
        $this->save();
        return $this;
    }

    public function removeExtra(mixed $extra): self
    {
        $this->composer->removeExtra($extra);
        unset($this->composerJsonData['extra'][$extra]);
        $this->save();
        return $this;
    }

    public function removeRequireDev(Package $package): self
    {
        $this->composer->removeRequireDev($package);
        $this->save();
        return $this;
    }

    /**
     * @param ArrayCollection<int, string> $classMap
     * @return $this
     * @description only to be used in transition to PSR4 compliance
     */
    public function setAutoloadClassMap(ArrayCollection $classMap): self
    {
        $this->composer->setAutoload($classMap);
        $this->composerJsonData['autoload']['classmap'] = $classMap->toArray();
        $this->save();
        return $this;
    }

    /**
     * @return $this
     */
    public function addPsr4Autoload(Autoload $autoload): self
    {
        $this->composer->addAutoload($autoload);
        $this->composerJsonData['autoload']['psr-4'][$autoload->getNamespace()] = $autoload->getPath();
        $this->save();
        return $this;
    }

    /**
     * @param ArrayCollection<int, Autoload> $autoload
     * @return $this
     */
    public function setAutoload(ArrayCollection $autoload): self
    {
        $this->composer->setAutoload($autoload);
        $this->composerJsonData['autoload']['psr-4'] = [];
        $autoload->map(fn(Autoload $autoload): string => $this->composerJsonData['autoload']['psr-4'][$autoload->getNamespace()] = $autoload->getPath());
        $this->save();
        return $this;
    }

    /**
     * @param ArrayCollection<int, Package> $require
     * @return $this
     */
    public function setRequire(ArrayCollection $require): self
    {
        $this->composer->setRequire($require);
        $this->composerJsonData['require'] = [];
        $require->map(fn(Package $package): string => $this->composerJsonData['require'][$package->getName()] = $package->getPackageVersion()->getVersionString());
        $this->save();
        return $this;
    }

    /**
     * @param ArrayCollection<int, Package> $require
     * @return $this
     */
    public function setRequireDev(ArrayCollection $require): self
    {
        $this->composer->setRequireDev($require);
        $this->composerJsonData['require-dev'] = [];
        $require->map(fn(Package $package): string => $this->composerJsonData['require-dev'][$package->getName()] = $package->getPackageVersion()->getVersionString());
        $this->save();
        return $this;
    }

    /**
     * @param ArrayCollection<int, Package> $replace
     * @return $this
     */
    public function setReplace(ArrayCollection $replace): self
    {
        $this->composer->setReplace($replace);
        $this->composerJsonData['replace'] = [];
        $replace->map(fn(Package $package): string => $this->composerJsonData['replace'][$package->getName()] = $package->getPackageVersion()->getVersionString());
        $this->save();
        return $this;
    }

    /**
     * @param ArrayCollection<int, Package> $config
     * @return $this
     */
    public function setConfig(ArrayCollection $config): self
    {
        $this->composer->setConfig($config);
        $this->composerJsonData['config'] = [];
        $config->map(fn(Package $package): string => $this->composerJsonData['config'][$package->getName()] = $package->getPackageVersion()->getVersionString());
        $this->save();
        return $this;
    }

    /**
     * @param ArrayCollection<int, Script> $scripts
     * @return $this
     */
    public function setScripts(ArrayCollection $scripts): self
    {
        $this->composer->setScripts($scripts);
        $this->composerJsonData['scripts'] = [];
        $scripts->map(fn(Script $script): string => $this->composerJsonData['scripts'][$script->getName()] = $script->getCommand());
        $this->save();
        return $this;
    }

    /**
     * @param ArrayCollection<int, Package> $conflicts
     * @return $this
     */
    public function setConflict(ArrayCollection $conflicts): self
    {
        $this->composer->setConflict($conflicts);
        $this->composerJsonData['conflict'] = [];
        $conflicts->map(fn(Package $package): string => $this->composerJsonData['conflict'][$package->getName()] = $package->getPackageVersion()->getVersionString());
        $this->save();
        return $this;
    }

    private function save(): void
    {
        $this->removeEmptySections($this->composerJsonData);
        file_put_contents($this->getFilePath(), json_encode($this->composerJsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function removeEmptySections(&$array): void
    {
        foreach ($array as $section => &$content) {
            if (is_array($content)) {
                $this->removeEmptySections($content);
                if (empty($content)) {
                    unset($array[$section]);
                }
            }
        }
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }
}
