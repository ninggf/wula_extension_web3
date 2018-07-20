<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\web3;

use Web3\Formatters\QuantityFormatter;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use wulaphp\conf\ConfigurationLoader;

class Web3Factory {
	/**
	 * 根据配置(在web3_config.php中)获取一个Web3实现.
	 *
	 * @param string $cfg 配置.
	 *
	 * @return Web3
	 */
	public static function newWeb3(string $cfg = 'default'): Web3 {
		static $cfgs = false;
		if ($cfgs === false) {
			$cfgs = ConfigurationLoader::loadFromFile('web3');
		}
		$conf = $cfgs->get($cfg);
		if (!$conf) {
			$conf = ['url' => 'https://localhost:8545'];
		} else if (!is_array($conf)) {
			$conf = ['url' => $conf];
		}
		$timeout  = intval($conf['timeout'] ?? 5);
		$rqmgr    = new HttpRequestManager($conf['url'] ?? 'http://localhost:8545', $timeout);
		$provider = new HttpProvider($rqmgr);
		$web3     = new Web3($provider);
		$web3->Id = $cfg;
		//从哪个区块开始同步
		$web3->startBlockId = $conf['startBlockId'] ?? false;

		return $web3;
	}

	/**
	 * 同步区块信息.
	 * @return array 同步到区块ID
	 */
	public static function sync(): array {
		$cfgs          = ConfigurationLoader::loadFromFile('web3');
		$providers     = $cfgs->toArray();
		$lastSyncedIds = [];
		foreach ($providers as $provider => $cfg) {
			//读取最后更新的blockid.
			$syncDataFile      = TMP_PATH . '.ethereum.sync.' . $provider;
			$lastSyncedBlockId = @file_get_contents($syncDataFile);
			if ($lastSyncedBlockId) {
				$lastSyncedBlockId = base_convert($lastSyncedBlockId, 16, 10);
			} else {
				//从配置中读取同步起点
				$lastSyncedBlockId = $cfg['startBlockId'] ?? false;
				if (!$lastSyncedBlockId) {
					continue;
				}
				$lastSyncedBlockId -= 1;
			}
			$fullTranscationData = boolval($cfg['fullTxData'] ?? false);
			$web3                = self::newWeb3($provider);
			//最新高度
			$latestBlockId = $web3->getLatestBlockNumber();
			//一直同步到最新区块
			while ($lastSyncedBlockId < $latestBlockId) {
				$lastSyncedBlockId += 1;//下一个区块编号
				$block             = $web3->getBlock(QuantityFormatter::format($lastSyncedBlockId), $fullTranscationData);
				if (!$block) {
					log_warn($web3->lastError, 'ethereum.sync');
					break;
				}
				try {
					fire('ethereum\onBlockSynced', $web3, $block);
				} catch (\Exception $e) {

				} catch (\Error $er) {

				}
			}
			$lastSyncedIds[ $provider ] = $lastSyncedBlockId;
			@file_put_contents($syncDataFile, '0x' . base_convert($lastSyncedBlockId, 10, 16));
		}

		return $lastSyncedIds;
	}
}