<?php

require_once 'Turing/TuringTestConfig.php';
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @package   Turing
 * @copyright 2012-2016 silverorange
 */
abstract class TuringAtomTest extends PHPUnit_Framework_TestCase
{
	// {{{ protected properties

	/**
	 * @var TuringTestConfig
	 */
	protected $config;

	/**
	 * @var DOMXPath
	 */
	protected $xpath;

	/**
	 * @var DOMDocument
	 */
	protected $document;

	/**
	 * @var array
	 */
	protected $request_info;

	/**
	 * @var string
	 */
	protected $location;

	// }}}
	// {{{ public function __construct()

	public function __construct(
		$name = null,
		array $data = array(),
		$data_name = ''
	)
	{
		parent::__construct($name, $data, $data_name);
		$this->config = $this->getConfig();
	}

	// }}}
	// {{{ public function setUp()

	public function setUp()
	{
		$this->config->setUp();
	}

	// }}}
	// {{{ public function tearDown()

	public function tearDown()
	{
		$this->config->tearDown();
	}

	// }}}
	// {{{ protected function load()

	protected function load($uri)
	{
		if (preg_match('/^https?:/i', $uri) === 0) {
			$uri = $this->config->getBaseHref().$uri;
		}

		$curl = curl_init($uri);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$feed = curl_exec($curl);
		$this->request_info = curl_getinfo($curl);
		curl_close($curl);

		$this->location = $uri;

		$this->document = new DOMDocument();
		$this->document->resolveExternals = true;
		$this->document->loadXml($feed);

		$this->xpath = new DOMXPath($this->document);
		$this->xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
		$this->xpath->registerNamespace('html', 'http://www.w3.org/1999/xhtml');
	}

	// }}}
	// {{{ protected function loadHtml()

	protected function loadHtml($uri)
	{
		if (preg_match('/^https?:/i', $uri) === 0) {
			$uri = $this->config->getBaseHref().$uri;
		}

		$curl = curl_init($uri);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$html = curl_exec($curl);
		$this->request_info = curl_getinfo($curl);
		curl_close($curl);

		$this->location = $uri;

		$tidy = new Tidy();
		$tidy->parseString($html, array(
			'output-xhtml'     => true,
			'char-encoding'    => 'utf8',
			'numeric-entities' => true,
		), 'utf8');

		$tidy->cleanRepair();

		$this->document = new DOMDocument();
		$this->document->resolveExternals = true;
		$this->document->loadXml($tidy);

		$this->xpath = new DOMXPath($this->document);
		$this->xpath->registerNamespace('atom', 'http://www.w3.org/2005/Atom');
		$this->xpath->registerNamespace('html', 'http://www.w3.org/1999/xhtml');
	}

	// }}}
	// {{{ protected function getConfig()

	protected function getConfig()
	{
		return new TestConfig(
			$this,
			$this->getConfigFilename()
		);
	}

	// }}}
	// {{{ abstract protected function getConfigFilename()

	abstract protected function getConfigFilename();

	// }}}

	// shared assertions
	// {{{ protected function assertNoErrors()

	protected function assertNoErrors()
	{
		$this->assertNoExceptions();
		$this->assertNoXDebugErrors();
	}

	// }}}
	// {{{ protected function assertNoExceptions()

	protected function assertNoExceptions()
	{
		$list = $this->xpath->query("//html:div[@class='swat-exception']");
		$this->assertEquals(
			0,
			$list->length,
			'One or more exceptions are present in the feed.'
		);
	}

	// }}}
	// {{{ protected function assertNoXDebugErrors()

	protected function assertNoXDebugErrors()
	{
		$list = $this->xpath->query("//html:table[@class='xdebug-error']");
		$this->assertEquals(
			0,
			$list->length,
			'One or more xdebug errors are present in the feed.'
		);
	}

	// }}}
	// {{{ protected function assertFeedElementsPresent()

	protected function assertFeedElementsPresent()
	{
		$list = $this->xpath->query("/atom:feed/atom:generator");
		$this->assertEquals(
			1,
			$list->length,
			'Feed does not have exactly 1 generator.'
		);

		$list = $this->xpath->query("/atom:feed/atom:id");
		$this->assertEquals(
			1,
			$list->length,
			'Feed does not have exactly 1 id.'
		);

		$list = $this->xpath->query("/atom:feed/atom:link[@rel='self']");
		$this->assertEquals(
			1,
			$list->length,
			'Feed does not have exactly 1 "self" link.'
		);

		$list = $this->xpath->query("/atom:feed/atom:subtitle");
		$this->assertEquals(
			1,
			$list->length,
			'Feed does not have exactly 1 subtitle.'
		);

		$list = $this->xpath->query("/atom:feed/atom:title");
		$this->assertEquals(
			1,
			$list->length,
			'Feed does not have exactly 1 title.'
		);

		$list = $this->xpath->query("/atom:feed/atom:updated");
		$this->assertEquals(
			1,
			$list->length,
			'Feed does not have exactly 1 updated date.'
		);
	}

	// }}}
	// {{{ protected function assertEntryElementsPresent()

	protected function assertEntryElementsPresent()
	{
		$list = $this->xpath->query("/atom:feed/atom:entry");
		$this->assertNotEquals(
			0,
			$list->length,
			'Feed does not contain any entries.'
		);

		$list = $this->xpath->query("/atom:feed/atom:entry/atom:author");
		$this->assertNotEquals(
			0,
			$list->length,
			'Entries do not have any author.'
		);

		$list = $this->xpath->query(
			"/atom:feed/atom:entry/atom:author/atom:name"
		);

		$this->assertNotEquals(
			0,
			$list->length,
			'Authors dot not have any name.'
		);

		$list = $this->xpath->query("/atom:feed/atom:entry/atom:content");
		$this->assertNotEquals(
			0,
			$list->length,
			'Entries do not have any content.'
		);

		$list = $this->xpath->query("/atom:feed/atom:entry/atom:id");
		$this->assertNotEquals(
			0,
			$list->length,
			'Entries do not have ids.'
		);

		$list = $this->xpath->query(
			"/atom:feed/atom:entry/atom:link[@rel='alternate']"
		);

		$this->assertNotEquals(
			0,
			$list->length,
			'Entries do not have alternative links.'
		);

		$list = $this->xpath->query("/atom:feed/atom:entry/atom:title");
		$this->assertNotEquals(
			0,
			$list->length,
			'Entries do not have titles.'
		);

		$list = $this->xpath->query("/atom:feed/atom:entry/atom:updated");
		$this->assertNotEquals(
			0,
			$list->length,
			'Entries do not have updated dates.'
		);
	}

	// }}}
	// {{{ protected function assertPaginationWorks()

	protected function assertPaginationWorks()
	{
		// get next page
		$href = $this->xpath->evaluate(
			"string(/atom:feed/atom:link[@rel='next']/@href)"
		);

		$this->assertNotEquals(
			'',
			$href,
			'No next link present in paginated feed.'
		);

		// load next page
		$this->load($href);
		$this->assertNoErrors();

		// get last page
		$href = $this->xpath->evaluate(
			"string(/atom:feed/atom:link[@rel='last']/@href)"
		);

		$this->assertNotEquals(
			'',
			$href,
			'No last link present in paginated feed'
		);

		// load last page
		$this->load($href);
		$this->assertNoErrors();

		// make sure there is no next page
		$list = $this->xpath->query("/atom:feed/atom:link[@rel='next']");
		$this->assertEquals(
			0,
			$list->length,
			'Last page in feed contains a next link.'
		);

		// get prev page
		$href = $this->xpath->evaluate(
			"string(/atom:feed/atom:link[@rel='previous']/@href)"
		);

		$this->assertNotEquals(
			'',
			$href,
			'No prev link present on last page of paginated feed.'
		);

		// load prev page
		$this->load($href);
		$this->assertNoErrors();

		// get first page
		$href = $this->xpath->evaluate(
			"string(/atom:feed/atom:link[@rel='first']/@href)"
		);

		$this->assertNotEquals(
			'',
			$href,
			'No first link present in paginated feed.'
		);

		// load first page
		$this->load($href);
		$this->assertNoErrors();
	}

	// }}}
	// {{{ protected function assertNotFound()

	protected function assertNotFound()
	{
		// make sure there was an exception
		$list = $this->xpath->query("//html:div[@class='swat-exception']");
		$this->assertNotEquals(
			0,
			$list->length,
			'Expected "not found" exception was not encountered.'
		);

		// make sure response code was 404
		$this->assertEquals(
			404,
			$this->request_info['http_code'],
			'Response code of "not found" page was not 404.'
		);
	}

	// }}}
}

?>
