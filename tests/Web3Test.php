<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\web3\tests;

use phpseclib\Math\BigInteger;
use PHPUnit\Framework\TestCase;
use Web3\Formatters\QuantityFormatter;
use Web3\Utils;
use wula\web3\Web3Factory;
use wulaphp\conf\ConfigurationLoader;

class Web3Test extends TestCase {

	public static function setUpBeforeClass() {
		$cfg = ConfigurationLoader::loadFromFile('web3');
		$cfg->setConfigs([
			'nodes' => [
				'default' => [
					'url'          => 'http://localhost:8545',
					'startBlockId' => 1
				]
			]
		]);
	}

	public static function tearDownAfterClass() {
		unlink(TMP_PATH . '.ethereum.sync.default');
	}

	public function testConnect() {
		$web3 = Web3Factory::newWeb3();
		$web3->eth->getBalance('0xcfCf770425DFa579d305Edf612F271878bAfe70A', function ($err, ?BigInteger $rst) {
			self::assertEmpty($err, 2);
			self::assertNotEmpty($rst);
			self::assertEquals('100', Utils::toEther($rst, 'wei')[0]->toString());
		});
	}

	public function testBlockNumber() {
		$web3 = Web3Factory::newWeb3();
		self::assertEquals(1, $web3->startBlockId);
		$web3->eth->blockNumber(function ($err, ?BigInteger $rst) use (&$blockid) {
			self::assertEmpty($err);
			self::assertNotEmpty($rst);
			$blockid = $rst->toString();
		});

		$newBid = $web3->getLatestBlockNumber();
		self::assertTrue($newBid >= 0, 'real blockid is: ' . $newBid);
		self::assertEquals($newBid, $blockid);

		$web3->eth->getBlockByNumber(QuantityFormatter::format($blockid), false, function ($err, \stdClass $rst) {
			self::assertEmpty($err);
			self::assertNotEmpty($rst);
			self::assertNotEmpty($rst->nonce);
			self::assertNotEmpty($rst->transactions);
		});
	}

	public function testSync() {
		unbind_all('ethereum\onBlockSynced');
		$ids = Web3Factory::sync();
		self::assertNotEmpty($ids);
		self::assertArrayHasKey('default', $ids);
		$web3 = Web3Factory::newWeb3('default');
		$lid  = $web3->getLatestBlockNumber();
		self::assertEquals($ids['default'], $lid);
	}
}