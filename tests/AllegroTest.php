<?php

namespace clagiordano\MarketplacesDataExport\Tests;

use clagiordano\MarketplacesDataExport\Adapters\Allegro;
use clagiordano\MarketplacesDataExport\Config;

/**
 * Class AllegroTest
 * @package clagiordano\MarketplacesDataExport\Tests
 */
class AllegroTest extends \PHPUnit_Framework_TestCase
{
    /** @var Allegro $adapter */
    protected $adapter = null;

    public function setUp()
    {
        $configFile = __DIR__ . '/../testdata/allegro.php';
        $this->assertFileExists($configFile);

        $config = new Config($configFile);
        $this->assertInstanceOf('clagiordano\MarketplacesDataExport\Config', $config);

        $this->adapter = new Allegro($config, 'https://webapi.allegro.pl/service.php?wsdl');
        $this->assertInstanceOf(
            'clagiordano\MarketplacesDataExport\Adapters\Allegro',
            $this->adapter
        );
    }

    /**
     * @group internal
     * @group info
     */
    public function testGetApiInfo()
    {
        $this->assertInternalType('array', $this->adapter->getApiInfo());
        $this->assertNotNull($this->adapter->getApiInfo()['verKey']);
    }

    /**
     * @group internal
     * @group login
     */
    public function testDoLogin()
    {
        $this->adapter->doLogin();
    }

    /**
     * @group public
     * @group sells
     */
    public function testGetMySells()
    {
        $sells = $this->adapter->getMySellItems();
        $this->assertInternalType('array', $sells);
    }

    /**
     * @group public
     * @group solds
     */
    public function testGetMySolds()
    {
        $sells = $this->adapter->getMySoldItems();
        $this->assertInternalType('array', $sells);

        $this->assertArrayHasKey('soldItemsList', $sells);
        $this->assertInternalType('array', $sells['soldItemsList']);

        $this->assertArrayHasKey('item', $sells['soldItemsList']);
        $this->assertInternalType('array', $sells['soldItemsList']['item']);
    }

    /**
     * @group public
     * @group user
     */
    public function testGetUserInfoByUserId()
    {
        $info = $this->adapter->getUserInfo(336686);
        $this->assertInternalType('array', $info);
    }

    /**
     * @group public
     * @group deals
     */
    public function testGetDeals()
    {
        $sells = $this->adapter->getMySoldItems();

        foreach ($sells['soldItemsList'] as $item) {
            $this->assertArrayHasKey('itemId', $item);

            $this->assertArrayHasKey('itemHighestBidder', $item);
            $this->assertInternalType('array', $item['itemHighestBidder']);

            $this->assertArrayHasKey('userId', $item['itemHighestBidder']);
            $this->assertArrayHasKey('userLogin', $item['itemHighestBidder']);

            var_dump($item['itemId']);
//            var_dump($item['itemHighestBidder']['userId']);

//            $userInfo = $this->adapter->getUserInfo($item['itemHighestBidder']['userId']);
//            print_r($userInfo);

//            $deals = $this->adapter->getDeals($item['itemId'], $item['itemHighestBidder']['userId']);
//            print_r($deals);
            $buyerData = $this->adapter->getBuyerData([$item['itemId']]);
            print_r($buyerData);
        }
    }

    /**
     * @group public
     * @group deals
     * @group journal
     */
    public function testGetJournalDeals()
    {
        $response = $this->adapter->getJournalDeals();
        $this->assertInternalType('array', $response);

        $this->assertArrayHasKey('siteJournalDeals', $response);
        $this->assertInternalType('array', $response['siteJournalDeals']);
    }

    /**
     * @group public
     * @group deals
     * @group transactions
     */
    public function testGetTransactionsData()
    {
        $transactionIds = [];
        $response = $this->adapter->getJournalDeals();
        print_r($response);

        // Filter for dealEventId
//        $response = $this->adapter->getJournalDeals(2887265130);
//        print_r($response);

        foreach ($response['siteJournalDeals']['item'] as $item) {
            $this->assertInternalType('array', $item);
            $this->assertArrayHasKey('dealTransactionId', $item);

//            $transaction = $this->adapter->getTransactionsData([$item['dealTransactionId']]);
//            print_r($transaction);
            $transactionData = $this->adapter->getTransactionsData($item['dealTransactionId']);
            var_dump($transactionData);

            $transactionIds[] = $item['dealTransactionId'];
        }

//        $transaction = $this->adapter->getTransactionsIds([6674148513, 667339875]);
        $transaction = $this->adapter->getTransactionsData($transactionIds);
        var_dump($transaction);
    }
}