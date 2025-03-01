# Composer Json Parser

A tool that converts your composer.json file to an object and allows you to find any composer data quickly.

### Install

`composer require kerrialn/composer-json-parser`

### Usage

Basic usage

````php
use KerrialNewham\ComposerJsonParser\ComposerJson;

$composer = (new ComposerJson())->getComposer()
// Will find composer.json create a Composer object 
````

With composer.json path

````php
use KerrialNewham\ComposerJsonParser\ComposerJson;

$composer = (new ComposerJson())
    ->withComposerJsonPath(path: 'composer/json/directory/')
    ->getComposer()
// Will return a Composer object 
````

Don't need to load everything, only what you need:
````php
    use KerrialNewham\ComposerJsonParser\ComposerJson;

    $composer = (new ComposerJson())
    ->withName()
    ->withRequire()
    ->withAutoload()
    ->getComposer()
    // Will return a Composer object 
````

Need to find a specific package?
````php
$composer = (new ComposerJson())->withRequire()->getComposer();
$doctrineOrmPackage = $composer->getRequire()->findFirst(fn (int $key, Package $package) =>  $package->getName() == 'php');
````

Need to modify the composer.json?
````php
use KerrialNewham\ComposerJsonParser\ComposerJson;
use KerrialNewham\ComposerJsonParser\Model\Autoload;
    
$autoload = new Autoload(namespace: 'App\\', path: './src');
$composer = (new ComposerJson())->addPsr4Autoload($autoload)
// Will update composer.json with new PSR4 Autoload namespace
````

What about add a package programmatically?
````php
use KerrialNewham\ComposerJsonParser\ComposerJson;
use KerrialNewham\ComposerJsonParser\Model\Autoload;
    
$packageVersion = new PackageVersion(versionString: '^2.0', version: 2.0, versionConstraints: '^');
$composerJsonPackage = new Package(name: 'kerrialn/composer-json-parser', type: PackageTypeEnum::REQUIRE, packageVersion: $packageVersion);

(new ComposerJson())->withComposerJsonPath()->addRequire(package: $composerJsonPackage);
// Will update composer.json with new package.
````