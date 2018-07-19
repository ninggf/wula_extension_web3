<?php
/*
 * This file is part of wulacms.
 *
 * (c) Leo Ning <windywany@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

#
# 同步区块信息脚本（可以5秒运行一次）
#
# php artisan cron -i5 extensions/wula/ethereum/sync.php?cfg=default

include __DIR__ . '/../../../bootstrap.php';
//同步区块信息
\wula\web3\Web3Factory::sync();

exit(0);