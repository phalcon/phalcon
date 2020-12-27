<?php 
namespace Phalcon\Tests\Unit\Db;
use Phalcon\Db;
use Phalcon\Db\Adapter\Pdo\Mysql as MysqlConnection;

use UnitTester;

class ConnectCest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;
    
    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testSomeFeature()
    {

    }

    // 

    public function dbConnect(UnitTester $I)
    {
        $I->wantToTest('Collection - __construct()');

        $collection = new Collection();

        $I->assertInstanceOf(
            Collection::class,
            $collection
        );
    }
}