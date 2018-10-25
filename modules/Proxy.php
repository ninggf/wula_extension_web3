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
use Web3\Formatters\QuantityFormatter;
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
        return $this->request('blockNumber');
    }

    /**
     * 最高区块.
     *
     * @return null|string
     */
    public function getLatestBlockNumber(): ?string {
        return $this->blockNumber();
    }

    /**
     * 根据区块ID获取区块数据.
     *
     * @param string $number 16进制
     * @param bool   $fulltx
     *
     * @return null|string
     */
    public function getBlockByNumber(string $number, bool $fulltx = false): ?string {
        if (!preg_match('/^0x/', $number)) {
            $number = QuantityFormatter::format($number);
        }

        return $this->request('getBlockByNumber', ['tag' => $number, 'boolean' => $fulltx ? 'true' : 'false']);
    }

    /**
     * @param string $blockId
     * @param bool   $fulltx
     *
     * @return null|\stdClass
     */
    public function getBlock(string $blockId = '0', bool $fulltx = false): ?\stdClass {
        $rst = $this->getBlockByNumber($blockId, $fulltx);
        if ($rst) {
            return json_decode($rst);
        }

        return null;
    }

    /**
     * 根据交易hash获取交易数据.
     *
     * @param string $hash
     *
     * @return null|string
     */
    public function getTransactionByHash(string $hash): ?string {
        return $this->request('getTransactionByHash', ['txhash' => $hash]);
    }

    /**
     * 根据区块ID获取交易数量.
     *
     * @param string $number
     *
     * @return null|string
     */
    public function getBlockTransactionCountByNumber(string $number): ?string {
        if (!preg_match('/^0x/', $number)) {
            $number = QuantityFormatter::format($number);
        }

        return $this->request('getBlockTransactionCountByNumber', ['tag' => $number]);
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
        $rst = $this->getTransactionByHash($hash);
        if ($rst) {
            return json_decode($rst);
        }

        return null;
    }

    /**
     * 请求.
     *
     * @param string $action
     * @param array  $args
     *
     * @return null|string
     */
    private function request($action, $args = []): ?string {
        $url = $this->url . '&action=eth_' . $action;
        if ($args) {
            $url .= '&' . http_build_query($args);
        }
        $client = Curlient::factory();
        $rtn    = $client->get($url)->json();
        $client->close();
        if ($rtn && isset($rtn['jsonrpc']) && isset($rtn['result'])) {
            return is_array($rtn['result']) ? json_encode($rtn['result']) : $rtn['result'];
        }

        return null;
    }
}