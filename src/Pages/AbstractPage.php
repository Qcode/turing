<?php

namespace Silverorange\Turing\Pages;

use Facebook\WebDriver\WebDriver;
use Silverorange\Turing\WebDriver\Context;
use Silverorange\Turing\WebDriver\ContextSugarTrait;
use League\Uri\Interfaces\Uri as UriInterface;

/**
 * @package   Turing
 * @copyright 2017 silverorange
 * @license   http://www.gnu.org/copyleft/lesser.html LGPL License 2.1
 */
abstract class AbstractPage
{
    use ContextSugarTrait;

    // {{{ protected properties

    /**
     * @var Silverorange\Turing\WebDriver\Context
     */
    protected $context = null;

    /**
     * @var Facebook\WebDriver\WebDriver
     */
    protected $wd = null;

    /**
     * @var League\Uri\Interfaces\Uri
     */
    protected $url = null;

    // }}}
    // {{{ public function __construct()

    public function __construct(WebDriver $wd, UriInterface $url)
    {
        $this->wd = $wd;
        $this->context = new Context($wd);
        $this->url = $url;
    }

    // }}}
    // {{{ public function getTitle()

    public function getTitle()
    {
        return $this->wd->getTitle();
    }

    // }}}
    // {{{ public function getURL()

    public function getURL()
    {
        return $this->wd->getCurrentURL();
    }

    // }}}
    // {{{ public function refresh()

    public function refresh()
    {
        $this->wd->navigate()->refresh();
    }

    // }}}
    // {{{ public function load()

    public function load()
    {
        $this->wd->get($this->url);
    }

    // }}}
}
