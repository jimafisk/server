<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2016, ownCloud, Inc.
 *
 * @author Joas Schilling <coding@schilljs.com>
 * @author Lukas Reschke <lukas@statuscode.ch>
 * @author Morris Jobke <hey@morrisjobke.de>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\UpdateNotification\Tests\Settings;

use OCA\UpdateNotification\Settings\Admin;
use OCA\UpdateNotification\UpdateChecker;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IDateTimeFormatter;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\Util;
use Test\TestCase;

class AdminTest extends TestCase {
	/** @var Admin */
	protected $admin;
	/** @var IConfig|\PHPUnit_Framework_MockObject_MockObject */
	protected $config;
	/** @var UpdateChecker|\PHPUnit_Framework_MockObject_MockObject */
	protected $updateChecker;
	/** @var IGroupManager|\PHPUnit_Framework_MockObject_MockObject */
	protected $groupManager;
	/** @var IDateTimeFormatter|\PHPUnit_Framework_MockObject_MockObject */
	protected $dateTimeFormatter;

	public function setUp() {
		parent::setUp();

		$this->config = $this->createMock(IConfig::class);
		$this->updateChecker = $this->createMock(UpdateChecker::class);
		$this->groupManager = $this->createMock(IGroupManager::class);
		$this->dateTimeFormatter = $this->createMock(IDateTimeFormatter::class);

		$this->admin = new Admin(
			$this->config,
			$this->updateChecker,
			$this->groupManager,
			$this->dateTimeFormatter
		);
	}

	/**
	 * @return array with expected result
	 */
	public function mockFormParams($withSettings = true): array {
		$channels = [
			'daily',
			'beta',
			'stable',
			'production',
		];
		$currentChannel = Util::getChannel();
		if ($currentChannel === 'git') {
			$channels[] = 'git';
		}

		$this->config
			->expects($this->exactly(2))
			->method('getAppValue')
			->willReturnMap([
				['core', 'lastupdatedat', '', '12345'],
				['updatenotification', 'notify_groups', '["admin"]', '["admin"]'],
			]);
		$this->config
			->expects($this->once())
			->method('getSystemValue')
			->with('updater.server.url', 'https://updates.nextcloud.com/updater_server/')
			->willReturn('https://updates.nextcloud.com/updater_server/');
		$this->dateTimeFormatter
			->expects($this->once())
			->method('formatDateTime')
			->with('12345')
			->willReturn('LastCheckedReturnValue');
		$this->updateChecker
			->expects($this->once())
			->method('getUpdateState')
			->willReturn([
				'updateAvailable' => true,
				'updateVersion' => '8.1.2',
				'downloadLink' => 'https://downloads.nextcloud.org/server',
				'updaterEnabled' => true,
				'versionIsEol' => false,
			]);

		$group = $this->createMock(IGroup::class);
		$group->expects($this->any())
			->method('getDisplayName')
			->willReturn('Administrators');
		$group->expects($this->any())
			->method('getGID')
			->willReturn('admin');
		$this->groupManager->expects($this->once())
			->method('get')
			->with('admin')
			->willReturn($group);

		return [
			'json' => json_encode([
				'isNewVersionAvailable' => true,
				'isUpdateChecked' => true,
				'lastChecked' => 'LastCheckedReturnValue',
				'currentChannel' => Util::getChannel(),
				'channels' => $channels,
				'newVersionString' => '8.1.2',
				'downloadLink' => 'https://downloads.nextcloud.org/server',
				'updaterEnabled' => true,
				'versionIsEol' => false,
				'isDefaultUpdateServerURL' => true,
				'updateServerURL' => 'https://updates.nextcloud.com/updater_server/',
				'notifyGroups' => [
					['value' => 'admin', 'label' => 'Administrators'],
				],
				'withSettings' => $withSettings,
			]),
			'withSettings' => $withSettings,
		];
	}

	public function testGetFormWithUpdate() {
		$params = $this->mockFormParams(true);
		$expected = new TemplateResponse('updatenotification', 'admin', $params, '');
		$this->assertEquals($expected, $this->admin->getForm());
	}


	public function testGetSection() {
		$this->assertSame('server', $this->admin->getSection());
	}

	public function testGetPriority() {
		$this->assertSame(90, $this->admin->getPriority());
	}
}
