<?php
/**
 * TextParser test class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author    Sławomir Kłos <s.klos@yetiforce.com>
 */

namespace Tests\App;

class TextParser extends \Tests\Base
{
	/**
	 * Test record cache.
	 *
	 * @var \Vtiger_Record_Model
	 */
	private static $record;
	/**
	 * Test record instance.
	 *
	 * @var \App\TextParser
	 */
	private static $testInstanceRecord;
	/**
	 * Test clean instance.
	 *
	 * @var \App\TextParser
	 */
	private static $testInstanceClean;
	/**
	 * Test clean instance with module.
	 *
	 * @var \App\TextParser
	 */
	private static $testInstanceCleanModule;

	/**
	 * Creating leads module record for tests.
	 *
	 * @return \Vtiger_Record_Model
	 */
	public static function createLeadRecord()
	{
		$recordModel = \Vtiger_Record_Model::getCleanInstance('Leads');
		$recordModel->set('description', 'autogenerated test lead for \App\TextParser tests');
		$recordModel->save();
		return static::$record = $recordModel;
	}

	/**
	 * Testing instances creation.
	 */
	public function testInstancesCreation()
	{
		static::$testInstanceClean = \App\TextParser::getInstance();
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceClean, 'Expected clean instance without module of \App\TextParser');

		static::$testInstanceCleanModule = \App\TextParser::getInstance('Leads');
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceCleanModule, 'Expected clean instance with module Leads of \App\TextParser');

		$this->assertInstanceOf('\App\TextParser', \App\TextParser::getInstanceById(static::createLeadRecord()->getId(), 'Leads'), 'Expected instance from lead id and module string of \App\TextParser');

		static::$testInstanceRecord = \App\TextParser::getInstanceByModel(static::createLeadRecord());
		$this->assertInstanceOf('\App\TextParser', static::$testInstanceRecord, 'Expected instance from record model of \App\TextParser');
	}

	/**
	 * Tests empty content condition.
	 */
	public function testEmptyContent()
	{
		$this->assertSame('', static::$testInstanceClean
			->setContent('')
			->parse()
			->getContent(), 'Clean instance: empty content should return empty result');
	}

	/**
	 * Tests empty content condition.
	 */
	public function testUnregisteredPlaceholderFunction()
	{
		$this->assertSame('+  +', static::$testInstanceClean
			->setContent('+ $(notExist : CurrentTime)$ +')
			->parse()
			->getContent(), 'Clean instance: unregistered function placeholder should return empty string');
	}

	/**
	 * Tests general placeholders replacement.
	 */
	public function testGeneralPlaceholders()
	{
		$this->assertSame('+ ' . (new \DateTimeField(null))->getDisplayDate() . ' +', static::$testInstanceClean
			->setContent('+ $(general : CurrentDate)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : CurrentDate)$ should return current date');
		$this->assertSame('+ ' . \Vtiger_Util_Helper::convertTimeIntoUsersDisplayFormat(\date('H:i:s')) . ' +', static::$testInstanceClean
			->setContent('+ $(general : CurrentTime)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : CurrentTime)$ should return current time');
		$this->assertSame('+ ' . \AppConfig::main('default_timezone') . ' +', static::$testInstanceClean
			->setContent('+ $(general : BaseTimeZone)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : BaseTimeZone)$ should return system timezone');
		$user = \App\User::getCurrentUserModel();
		$this->assertSame('+ ' . ($user->getDetail('time_zone') ? $user->getDetail('time_zone') : \AppConfig::main('default_timezone')) . ' +', static::$testInstanceClean
			->setContent('+ $(general : UserTimeZone)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : UserTimeZone)$ should return user timezone');

		$this->assertSame('+ ' . \AppConfig::main('site_URL') . ' +', static::$testInstanceClean
			->setContent('+ $(general : SiteUrl)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : SiteUrl)$ should return site url');

		$this->assertSame('+ ' . \AppConfig::main('PORTAL_URL') . ' +', static::$testInstanceClean
			->setContent('+ $(general : PortalUrl)$ +')
			->parse()
			->getContent(), 'Clean instance: $(general : PortalUrl)$ should return portal url');

		$this->assertSame('+ Kopiuj adres korespondencji, sekund +', static::$testInstanceClean
			->setContent('+ $(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$, $(translate : LBL_SECONDS)$ +')
			->parse()
			->getContent(), 'Clean instance: $(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$, $(translate : LBL_SECONDS)$ should return translated string');
	}

	/**
	 * Tests date placeholders replacement.
	 */
	public function testDatePlaceholders()
	{
		$this->assertSame('+ ' . \date('Y-m-d') . ' +', static::$testInstanceClean
			->setContent('+ $(date : now)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : now)$ should return current date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('+1 day')) . ' +', static::$testInstanceClean
			->setContent('+ $(date : tomorrow)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : tomorrow)$ should return tommorow date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('-1 day')) . ' +', static::$testInstanceClean
			->setContent('+ $(date : yesterday)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : yesterday)$ should return yesterday date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('monday this week')) . ' +', static::$testInstanceClean
			->setContent('+ $(date : monday this week)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : monday this week)$ should return this week monday date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('monday next week')) . ' +', static::$testInstanceClean
			->setContent('+ $(date : monday next week)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : monday next week)$ should return next week monday date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('first day of this month')) . ' +', static::$testInstanceClean
			->setContent('+ $(date : first day of this month)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : first day of this month)$ should return this month first day date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('last day of this month')) . ' +', static::$testInstanceClean
			->setContent('+ $(date : last day of this month)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : last day of this month)$ should return this month last day date');

		$this->assertSame('+ ' . \date('Y-m-d', \strtotime('first day of next month')) . ' +', static::$testInstanceClean
			->setContent('+ $(date : first day of next month)$ +')
			->parse()
			->getContent(), 'Clean instance: $(date : first day of next month)$ should return next month first day date');
	}

	/**
	 * Testing basic field placeholder replacement.
	 */
	public function testBasicFieldPlaceholderReplacement()
	{
		\App\User::setCurrentUserId(1);
		$text = '+ $(employee : last_name)$ +';
		$this->assertSame('+  +', static::$testInstanceClean
			->setContent($text)
			->parse()
			->getContent(), 'Clean instance: By default employee last name should be empty');
		$this->assertSame('+  +', static::$testInstanceRecord
			->setContent($text)
			->parse()
			->getContent(), 'Record instance: By default employee last name should be empty');
	}

	/**
	 * Testing basic translate function.
	 */
	public function testTranslate()
	{
		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$testInstanceClean->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Clean instance: Translations should be equal');
		static::$testInstanceClean->withoutTranslations(true);

		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$testInstanceClean->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Clean instance: Translations should be equal');
		static::$testInstanceClean->withoutTranslations(false);

		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$testInstanceRecord->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Record instance: Translations should be equal');
		static::$testInstanceRecord->withoutTranslations(true);

		$this->assertSame(
			'+' . \App\Language::translate('LBL_SECONDS') . '==' . \App\Language::translate('LBL_COPY_BILLING_ADDRESS', 'Accounts') . '+',
			static::$testInstanceRecord->setContent('+$(translate : LBL_SECONDS)$==$(translate : Accounts|LBL_COPY_BILLING_ADDRESS)$+')->parse()->getContent(),
			'Record instance: Translations should be equal');
		static::$testInstanceRecord->withoutTranslations(false);
	}

	/**
	 * Testing basic source record related functions.
	 */
	public function testBasicSrcRecord()
	{
		$this->assertSame(
			'+autogenerated test lead for \App\TextParser tests+', static::$testInstanceClean->setContent('+$(sourceRecord : description)$+')->setSourceRecord(static::createLeadRecord()->getId())->parse()->getContent(),
			'Clean instance: Translations should be equal');

		$this->assertSame(
			'+autogenerated test lead for \App\TextParser tests+',
			static::$testInstanceRecord->setContent('+$(sourceRecord : description)$+')->setSourceRecord(static::createLeadRecord()->getId())->parse()->getContent(),
			'Record instance: Translations should be equal');
	}
}
