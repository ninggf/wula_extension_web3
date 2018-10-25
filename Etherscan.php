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

use wula\web3\modules\Proxy;

/**
 * Class Etherscan
 * @package wula\web3
 * @property-read \wula\web3\modules\Proxy $proxy
 */
class Etherscan {
    const HOST = 'https://api.etherscan.io/api';
    private $key     = '';
    private $modules = [];

    public function __construct(string $key) {
        $this->key = $key;
    }

    public function __get($name) {
        if (!array_key_exists($name, $this->modules)) {
            switch ($name) {
                case 'proxy':
                    $module = new Proxy($this->key);
                    break;
                default:
                    $module = null;
            }
            $this->modules[ $name ] = $module;
        }

        return $this->modules[ $name ];
    }
}