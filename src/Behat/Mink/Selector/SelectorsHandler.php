<?php

namespace Behat\Mink\Selector;

/*
 * This file is part of the Behat\Mink.
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Selectors handler.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class SelectorsHandler
{
    private $selectors;

    /**
     * Initializes selectors handler.
     *
     * @param SelectorInterface[] $selectors default selectors to register
     */
    public function __construct(array $selectors = array())
    {
        $this->registerSelector('named', new NamedSelector());
        $this->registerSelector('css', new CssSelector());

        foreach ($selectors as $name => $selector) {
            $this->registerSelector($name, $selector);
        }
    }

    /**
     * Registers new selector engine with specified name.
     *
     * @param string            $name     selector engine name
     * @param SelectorInterface $selector selector engine instance
     */
    public function registerSelector($name, SelectorInterface $selector)
    {
        $this->selectors[$name] = $selector;
    }

    /**
     * Checks whether selector with specified name is registered on handler.
     *
     * @param string $name selector engine name
     *
     * @return Boolean
     */
    public function isSelectorRegistered($name)
    {
        return isset($this->selectors[$name]);
    }

    /**
     * Returns selector engine with specified name.
     *
     * @param string $name selector engine name
     *
     * @return SelectorInterface
     *
     * @throws \InvalidArgumentException
     */
    public function getSelector($name)
    {
        if (!$this->isSelectorRegistered($name)) {
            throw new \InvalidArgumentException("Selector \"$name\" is not registered.");
        }

        return $this->selectors[$name];
    }

    /**
     * Translates selector with specified name to XPath.
     *
     * @param string       $selector selector engine name (registered)
     * @param string|array $locator  selector locator (an array or a string depending of the selector being used)
     *
     * @return string
     */
    public function selectorToXpath($selector, $locator)
    {
        if ('xpath' === $selector) {
            if (!is_string($locator)) {
                throw new \InvalidArgumentException('The xpath selector expects to get a string as locator');
            }

            return $locator;
        }

        return $this->getSelector($selector)->translateToXPath($locator);
    }

    /**
     * Translates string to XPath literal.
     *
     * @param string $s
     *
     * @return string
     */
    public function xpathLiteral($s)
    {
        if (false === strpos($s, "'")) {
            return sprintf("'%s'", $s);
        }

        if (false === strpos($s, '"')) {
            return sprintf('"%s"', $s);
        }

        $string = $s;
        $parts = array();
        while (true) {
            if (false !== $pos = strpos($string, "'")) {
                $parts[] = sprintf("'%s'", substr($string, 0, $pos));
                $parts[] = "\"'\"";
                $string = substr($string, $pos + 1);
            } else {
                $parts[] = "'$string'";
                break;
            }
        }

        return sprintf("concat(%s)", implode($parts, ','));
    }
}
