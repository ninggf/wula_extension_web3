## Web3适配器
为[sc0vu/web3.php](https://packagist.org/packages/sc0vu/web3.php)中的`Web3`提供一个简单的封装以适用于[wula/wulaphp](https://packagist.org/packages/wula/wulaphp).

### 安装

`composer require wula/web3`

### 配置

使用之前请配置文件: `conf/web3_config.php`:

```php
    return [
        'etherscanKey'=>'your apiKey issued by https://etherscan.io/',
        'nodes'=>[
            'default' => [
                'url'=>'http://localhost:8545',
                'timeout'=>5,
                'startBlockId'=>10000,
                'fullTxData'=>true
            ],
            'otherServer'=>[
                'url'=>'http://www.nidefuwuqi.com:8545',
                'timeout'=>5
            ]
        ]
    ]
```
> 说明:
> * etherscanKey: API KEY
> * nodes: 节点 
> * `url`: RPC服务器
> * `timeout`: 连接超时，单位秒
> * `startBlockId`: 从哪个区块开始同步数据，如果不设置则不会同步区块数据
> * `fullTxData`: 是否获取事务的详细数据

### 使用

*获取web3实例并使用*:

```php
    //连接默认（default）服务器
    $web3 = Web3Factory::newWeb3();
    //或者连接指定服务器
    $web3 = Web3Factory::newWeb3('otherServer');
    
    
    //1. 获取最新区块
    $block = $web3->getBlock();
    //2. 获取1000区块并包括详细信息
    $block = $web3->getBlock(QuantityFormatter::format(1000),true); 
```

*开启同步*:

使用`artisan cron`命令执行`sync.php`：

`# php artisan cron -i3 extensions/wula/web3/sync.php`

> 每3秒同步一次.

### 事件
每同步到一个区块数据时触发`ethereum\onBlockSynced`事件:

*参数说明*

> 1. `Web3 $web3` Web3 实例.
> 2. `blockNumber` 区块ID.
> 3. `stdClass $block` 区块数据或null,详见[eth_getBlockByHash文档](https://github.com/ethereum/wiki/wiki/JSON-RPC#eth_getblockbyhash).

### 感谢

本扩展使用了[sc0vu/web3.php](https://packagist.org/packages/sc0vu/web3.php)库,特此感谢.
