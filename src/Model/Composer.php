<?php

declare(strict_types=1);

namespace KerrialNewham\ComposerJsonParser\Model;

use Doctrine\Common\Collections\ArrayCollection;

final class Composer
{
    private null|string $name = null;

    private null|string $description = null;

    private null|string $type = null;

    private null|PackageVersion $version = null;

    private null|string $minimumStability = null;

    private ArrayCollection $require;

    private readonly ArrayCollection $config;

    private readonly ArrayCollection $autoload;

    private readonly ArrayCollection $devAutoload;

    private ArrayCollection $replace;

    private ArrayCollection $scripts;

    private ArrayCollection $conflict;

    private readonly ArrayCollection $extra;

    private ArrayCollection $requireDev;

    public function __construct()
    {
        $this->require = new ArrayCollection();
        $this->config = new ArrayCollection();
        $this->autoload = new ArrayCollection();
        $this->devAutoload = new ArrayCollection();
        $this->replace = new ArrayCollection();
        $this->scripts = new ArrayCollection();
        $this->conflict = new ArrayCollection();
        $this->extra = new ArrayCollection();
        $this->requireDev = new ArrayCollection();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getVersion(): ?PackageVersion
    {
        return $this->version;
    }

    public function setVersion(?PackageVersion $version): void
    {
        $this->version = $version;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getMinimumStability(): string
    {
        return $this->minimumStability;
    }

    public function setMinimumStability(string $minimumStability): void
    {
        $this->minimumStability = $minimumStability;
    }

    /**
     * @return ArrayCollection<int, Package>
     */
    public function getRequire(): ArrayCollection
    {
        return $this->require;
    }

    /**
     * @param ArrayCollection<int, Package> $require
     */
    public function setRequire(ArrayCollection $require): self
    {
        $this->require = $require;
        return $this;
    }

    public function addRequire(Package $package)
    {
        $this->require->add($package);
    }

    /**
     * @return ArrayCollection<int, Package>
     */
    public function getRequireDev(): ArrayCollection
    {
        return $this->requireDev;
    }

    /**
     * @param ArrayCollection<int, Package> $requireDev
     */
    public function setRequireDev(ArrayCollection $requireDev): self
    {
        $this->requireDev = $requireDev;
        return $this;
    }

    public function removeDevAutoload(Autoload $autoload): self
    {
        if ($this->devAutoload->contains($autoload)) {
            $this->devAutoload->removeElement($autoload);
        }
        return $this;
    }

    public function removeRequire(Package $package): self
    {
        if ($this->require->contains($package)) {
            $this->require->removeElement($package);
        }
        return $this;
    }

    public function removeRequireDev(Package $package): self
    {
        if ($this->requireDev->contains($package)) {
            $this->requireDev->removeElement($package);
        }
        return $this;
    }

    public function addDevRequire(Package $package)
    {
        $this->requireDev->add($package);
    }

    public function getConfig(): ArrayCollection
    {
        return $this->config;
    }

    /**
     * @param ArrayCollection<int, Package> $config
     */
    public function setConfig(ArrayCollection $config): self
    {
        $this->requireDev = $config;
        return $this;
    }

    public function removeConfig(Package $package): self
    {
        if ($this->config->contains($package)) {
            $this->config->removeElement($package);
        }
        return $this;
    }

    public function addConfig(string $key, string $value)
    {
        $this->config->set($key, $value);
    }

    /**
     * @return ArrayCollection<int, Autoload>
     */
    public function getAutoload(): ArrayCollection
    {
        return $this->autoload;
    }

    /**
     * @param ArrayCollection<int, Package> $autoload
     */
    public function setAutoload(ArrayCollection $autoload): self
    {
        $this->requireDev = $autoload;
        return $this;
    }

    public function addAutoload(Autoload $autoload): void
    {
        $this->autoload->add($autoload);
    }

    public function removeAutoload(Autoload $autoload): self
    {
        if ($this->autoload->contains($autoload)) {
            $this->autoload->removeElement($autoload);
        }
        return $this;
    }

    /**
     * @return ArrayCollection<int, Autoload>
     */
    public function getDevAutoload(): ArrayCollection
    {
        return $this->devAutoload;
    }

    public function addDevAutoload(Autoload $autoload): void
    {
        $this->devAutoload->add($autoload);
    }

    /**
     * @return ArrayCollection<int, Script>
     */
    public function getScripts(): ArrayCollection
    {
        return $this->scripts;
    }

    public function addScript(Script $script): void
    {
        $this->scripts->add($script);
    }

    public function removeScript(Script $script): self
    {
        if ($this->scripts->contains($script)) {
            $this->scripts->removeElement($script);
        }
        return $this;
    }

    /**
     * @param ArrayCollection<int, Script> $scripts
     * @return $this
     */
    public function setScripts(ArrayCollection $scripts): self
    {
        $this->scripts = $scripts;
        return $this;
    }

    public function getRequirePackageByName(string $name): null|Package
    {
        return $this->getRequire()->findFirst(fn(int $key, Package $package): bool => $name === $package->getName());
    }

    public function getDevPackageByName(string $name): null|Package
    {
        return $this->getRequireDev()->findFirst(fn(int $key, Package $package): bool => $name === $package->getName());
    }

    public function getPackageByName(string $name): null|Package
    {
        $packages = new ArrayCollection([
            ...$this->getRequire(),
            ...$this->getRequireDev(),
        ]);

        return $packages->findFirst(fn(int $key, Package $package): bool => $name === $package->getName());
    }

    /**
     * @return ArrayCollection<int, Package>
     */
    public function getReplace(): ArrayCollection
    {
        return $this->replace;
    }

    public function addReplace(Package $package): void
    {
        $this->replace->add($package);
    }

    public function removeReplace(Package $package): self
    {
        if ($this->replace->contains($package)) {
            $this->replace->removeElement($package);
        }
        return $this;
    }

    /**
     * @param ArrayCollection<int, Package> $replace
     */
    public function setReplace(ArrayCollection $replace): self
    {
        $this->replace = $replace;
        return $this;
    }

    /**
     * @return ArrayCollection<mixed>
     */
    public function getExtra(): ArrayCollection
    {
        return $this->extra;
    }

    public function addExtra(mixed $extra): void
    {
        $this->extra->add($extra);
    }

    public function removeExtra(mixed $extra): self
    {
        if ($this->extra->contains($extra)) {
            $this->extra->removeElement($extra);
        }
        return $this;
    }

    /**
     * @return ArrayCollection<int, Package>
     */
    public function getConflict(): ArrayCollection
    {
        return $this->conflict;
    }

    public function addConflict(Package $package): void
    {
        $this->conflict->add($package);
    }

    public function removeConflict(Package $package): self
    {
        if ($this->conflict->contains($package)) {
            $this->conflict->removeElement($package);
        }
        return $this;
    }

    /**
     * @param ArrayCollection<int, Package> $conflicts
     */
    public function setConflict(ArrayCollection $conflicts): self
    {
        $this->conflict = $conflicts;
        return $this;
    }
}
