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

use phpseclib\Math\BigInteger;

/**
 * Class Web3
 * @package wula\web3
 * @property \Web3\Eth      $eth
 * @property \Web3\Net      $net
 * @property \Web3\Personal $personal
 * @property \Web3\Shh      $shh
 * @property \Web3\Utils    $utils
 */
class Web3 extends \Web3\Web3 {
	public $Id;
	public $lastError    = '';
	public $startBlockId = 0;

	/**
	 * 转到最小单位
	 *
	 * @param string $eth
	 *
	 * @return string
	 */
	public static function toWei(string $eth): string {
		return bcmul($eth, bcpow('10', 18));
	}

	/**
	 * 从最小单位转成最大单位.
	 *
	 * @param string|\phpseclib\Math\BigInteger $wei
	 * @param int                               $scale
	 *
	 * @return string
	 */
	public static function fromWei($wei, int $scale = 8): string {
		if ($wei instanceof BigInteger) {
			$wei = $wei->toString();
		} else if (is_string($wei)) {
			if (preg_match('/^0x[a-f\d]+$/', $wei)) {
				$wei = base_convert($wei, 16, 10);
			}
		} else {
			$wei = '0';
		}

		return bcdiv($wei, bcpow('10', 18), $scale);
	}

	/**
	 * 获取最新区块ID.
	 * @link https://github.com/ethereum/wiki/wiki/JSON-RPC#eth_blocknumber
	 *
	 * @return string
	 */
	public function getLatestBlockNumber(): string {
		$blockid = -1;
		try {
			$this->getEth()->blockNumber(function ($err, ?BigInteger $rst) use (&$blockid) {
				if ($err) {
					$this->lastError = $err;
				} else {
					$blockid = $rst->toString();
				}
			});
		} catch (\Exception $e) {
			$this->lastError = $e->getMessage();
		} catch (\Error $err) {
			$this->lastError = $err->getMessage();
		}

		return $blockid;
	}

	/**
	 * 获取区块信息.
	 * @link https://github.com/ethereum/wiki/wiki/JSON-RPC#eth_getblockbyhash
	 *
	 * @param string $blockId integer of a block number, or the string "earliest", "latest" or "pending"
	 * @param bool   $fulltx  If true it returns the full transaction objects,
	 *                        if false only the hashes of the transactions.
	 *
	 * @return null|\stdClass
	 */
	public function getBlock(string $blockId = 'latest', bool $fulltx = false): ?\stdClass {
		$block = null;
		try {
			$this->getEth()->getBlockByNumber($blockId, $fulltx, function ($err, \stdClass $rst) use (&$block) {
				if ($err) {
					$this->lastError = $err;
				}
				$block = $rst;
			});
		} catch (\Exception $e) {
			$this->lastError = $e->getMessage();
		} catch (\Error $err) {
			$this->lastError = $err->getMessage();
		}

		return $block;
	}

	/**
	 * 获取交易数据.
	 * @link https://github.com/ethereum/wiki/wiki/JSON-RPC#eth_gettransactionbyhash
	 *
	 * @param string $hash 交易的HASH
	 *
	 * @return null|\stdClass
	 */
	public function getTransaction(string $hash): ?\stdClass {
		$tx = null;
		try {
			$this->getEth()->getTransactionByHash($hash, function ($err, \stdClass $rst) use (&$tx) {
				if ($err) {
					$this->lastError = $err;
				}
				$tx = $rst;
			});
		} catch (\Exception $e) {
			$this->lastError = $e->getMessage();
		} catch (\Error $err) {
			$this->lastError = $err->getMessage();
		}

		return $tx;
	}
}