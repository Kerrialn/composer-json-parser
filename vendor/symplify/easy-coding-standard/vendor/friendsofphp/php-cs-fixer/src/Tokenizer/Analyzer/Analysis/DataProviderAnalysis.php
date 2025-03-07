<?php

declare (strict_types=1);
/*
 * This file is part of PHP CS Fixer.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *     Dariusz Rumiński <dariusz.ruminski@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace PhpCsFixer\Tokenizer\Analyzer\Analysis;

use PhpCsFixer\Console\Application;
use PhpCsFixer\Utils;
final class DataProviderAnalysis
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var int
     */
    private $nameIndex;
    /** @var list<int> */
    private $usageIndices;
    /**
     * @param list<int> $usageIndices
     */
    public function __construct(string $name, int $nameIndex, array $usageIndices)
    {
        $arrayIsListFunction = function (array $array) : bool {
            if (\function_exists('array_is_list')) {
                return \array_is_list($array);
            }
            if ($array === []) {
                return \true;
            }
            $current_key = 0;
            foreach ($array as $key => $noop) {
                if ($key !== $current_key) {
                    return \false;
                }
                ++$current_key;
            }
            return \true;
        };
        if (!$arrayIsListFunction($usageIndices)) {
            Utils::triggerDeprecation(new \InvalidArgumentException(\sprintf('Parameter "usageIndices" should be a list. This will be enforced in version %d.0.', Application::getMajorVersion() + 1)));
        }
        $this->name = $name;
        $this->nameIndex = $nameIndex;
        $this->usageIndices = $usageIndices;
    }
    public function getName() : string
    {
        return $this->name;
    }
    public function getNameIndex() : int
    {
        return $this->nameIndex;
    }
    /**
     * @return list<int>
     */
    public function getUsageIndices() : array
    {
        return $this->usageIndices;
    }
}
