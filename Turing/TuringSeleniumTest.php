<?php

require_once 'Swat/SwatDate.php';
require_once 'Turing/TuringTestConfig.php';
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * @package   Turing
 * @copyright 2012-2016 silverorange
 */
abstract class TuringSeleniumTest extends PHPUnit_Extensions_SeleniumTestCase
{
	// {{{ protected properties

	/**
	 * @var TuringTestConfig
	 */
	protected $config;

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
	// {{{ public function assertPreConditions()

	public function assertPreConditions()
	{
		$this->windowMaximize();
	}

	// }}}
	// {{{ public function setUp()

	public function setUp()
	{
		parent::setUp();

		$this->config->setUp();

		if ($this->config->getScreenshotPath() != '' &&
			$this->config->getScreenshotUrl() != '') {

			$this->screenshotPath = $this->config->getScreenshotPath();
			$this->screenshotUrl = $this->config->getScreenshotUrl();

			$this->captureScreenshotOnFailure = true;
		}

		$this->setHost($this->config->getSeleniumHost());
		$this->setPort($this->config->getSeleniumPort());
		$this->setBrowser('*chrome');
		$this->setBrowserUrl($this->config->getBaseHref());
		$this->start();
	}

	// }}}
	// {{{ public function tearDown()

	public function tearDown()
	{
		parent::tearDown();
		$this->config->tearDown();
	}

	// }}}
	// {{{ protected function getConfig()

	protected function getConfig()
	{
		return new TuringTestConfig(
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
		$this->assertNoLESSErrors();
	}

	// }}}
	// {{{ protected function assertNoLESSErrors()

	protected function assertNoLESSErrors()
	{
		$this->assertFalse(
			$this->isElementPresent(
				'xpath=//div[@class=\'less-error-message\']'
			),
			'One or more LESS CSS errors are present on the page.'
		);
	}

	// }}}
	// {{{ protected function assertNoExceptions()

	protected function assertNoExceptions()
	{
		$this->assertFalse(
			$this->isElementPresent(
				'xpath=//div[@class=\'swat-exception\']'
			),
			'One or more exceptions are present on the page.'
		);
	}

	// }}}
	// {{{ protected function assertNoXDebugErrors()

	protected function assertNoXDebugErrors()
	{
		$this->assertFalse(
			$this->isElementPresent(
				'xpath=//table[@class=\'xdebug-error\']'
			),
			'One or more xdebug errors are present on the page.'
		);
	}

	// }}}
	// {{{ protected function assertNotFound()

	protected function assertNotFound()
	{
		$this->assertTrue(
			$this->isTextPresent(
				'Sorry, we couldn’t find the page you were looking for.'
			),
			'Expected "not found" message not present.'
		);
	}

	// }}}
	// {{{ protected function assertLocationEndsWith()

	protected function assertLocationEndsWith($location, $message)
	{
		$exp = preg_quote($location, '!');
		$exp = '!'.$location.'(\?.*)?$!';

		$this->assertRegExp(
			$exp,
			$this->getLocation(),
			$message
		);
	}

	// }}}

	// shared test actions
	// {{{ protected function waitForContainerTextToAppear()

	protected function waitForContainerTextToAppear(
		$container_id, $text, $timeout = 5000)
	{
		$this->waitForContainerText($container_id, $text, $timeout);

		$this->assertTrue(
			$this->isTextPresent($text),
			sprintf(
				'Text not found when it should appear: %s',
				$text
			)
		);
	}

	// }}}
	// {{{ protected function waitForContainerTextToDisappear()

	protected function waitForContainerTextToDisappear(
		$container_id, $text, $timeout = 5000)
	{
		$this->waitForContainerText($container_id, $text, $timeout, true);

		$this->assertFalse(
			$this->isTextPresent($text),
			sprintf(
				'Text found when it should disappear: %s',
				$text
			)
		);
	}

	// }}}
	// {{{ protected function selectSwatDateEntry()

	protected function selectSwatDateEntry($id, SwatDate $date)
	{
		static $month_map = array(
			'January'   => '01',
			'February'  => '02',
			'March'     => '03',
			'April'     => '04',
			'May'       => '05',
			'June'      => '06',
			'July'      => '07',
			'August'    => '08',
			'September' => '09',
			'October'   => '10',
			'November'  => '11',
			'December'  => '12',
		);

		$year  = $date->getYear();
		$month = $date->getMonth();
		$day   = $date->getDay();

		$year_select  = 'xpath=//select[@id=\''.$id.'_year\']';
		$month_select = 'xpath=//select[@id=\''.$id.'_month\']';
		$day_select   = 'xpath=//select[@id=\''.$id.'_day\']';

		// get year info
		$year_options = $this->getSelectOptions($year_select);
		$start_year   = $year_options[1];
		$end_year     = end($year_options);

		// get month info
		if ($this->isElementPresent($month_select)) {
			$month_options = $this->getSelectOptions($month_select);

			$start_month = preg_replace('/[^A-Za-z]/', '', $month_options[1]);
			$start_month = $month_map[$start_month];

			$end_month   = preg_replace('/[^A-Za-z]/', '', end($month_options));
			$end_month   = $month_map[$end_month];
		} else {
			$start_month = '01';
			$end_month   = '01';
		}

		// get day info
		if ($this->isElementPresent($day_select)) {
			$day_options = $this->getSelectOptions($day_select);
			$start_day   = sprintf('%02d', $day_options[1]);
			$end_day     = sprintf('%02d', end($day_options));
		} else {
			$start_day = '01';
			$end_day   = '01';
		}

		// validate ranges
		$start_date = new SwatDate($start_year.$start_month.$start_day);
		$end_date   = new SwatDate($end_year.$end_month.$end_day);

		$this->assertFalse(
			$date->before($start_date),
			'Date specified in test ('.$date.') is before the minimum '.
			'selectable date ('.$start_date.').'
		);

		$this->assertFalse(
			$date->after($end_date),
			'Date specified in test ('.$date.') is after the maximum '.
			'selectable date ('.$end_date.').'
		);

		// select year
		$year_index = $year - intval($start_year) + 1;
		$this->select($year_select, 'index='.$year_index);

		// select month
		if ($this->isElementPresent($month_select)) {
			$month_index = $month - intval($start_month) + 1;
			$this->select($month_select, 'index='.$month_index);
		}

		// select day
		if ($this->isElementPresent($day_select)) {
			$day_index = $day - intval($start_day) + 1;
			$this->select($day_select, 'index='.$day_index);
		}
	}

	// }}}
	// {{{ private function waitForContainerText()

	private function waitForContainerText(
		$container_id, $text, $timeout = 5000, $inverse = false)
	{
		$this->waitForCondition(
			sprintf(
				'var container = window.document.getElementById(%s);
				(container && container.textContent.indexOf(%s) %s -1);',
				SwatString::quoteJavascriptString($container_id),
				SwatString::quoteJavascriptString($text),
				($inverse) ? '===' : '>'
			),
			$timeout
		);
	}

	// }}}
}

?>
