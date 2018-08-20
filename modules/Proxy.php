<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace wula\web3\modules;

use curlient\Curlient;
use wula\web3\Etherscan;

class Proxy {
	private $url;

	public function __construct($key) {
		$this->url = Etherscan::HOST . '?module=proxy&apikey=' . $key;
	}

	/**
	 * 最高区块.
	 *
	 * @return null|string
	 */
	public function blockNumber(): ?string {
		$url    = $this->url . '&action=eth_blockNumber';
		$client = Curlient::factory();
		$rtn    = $client->get($url)->json();
		$client->close();
		if ($rtn && isset($rtn['jsonrpc']) && isset($rtn['result'])) {
			return $rtn['result'];
		}

		return null;
	}
}