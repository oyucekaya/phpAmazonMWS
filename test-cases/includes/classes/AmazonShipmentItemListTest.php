<?php

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2012-12-12 at 13:17:14.
 */
class AmazonShipmentItemListTest extends PHPUnit_Framework_TestCase {

    /**
     * @var AmazonShipmentItemList
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        resetLog();
        $this->object = new AmazonShipmentItemList('testStore', null, true, null, __DIR__.'/../../test-config.php');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }
    
    public function testSetUp(){
        $obj = new AmazonShipmentItemList('testStore', '77', true, null, __DIR__.'/../../test-config.php');
        
        $o = $obj->getOptions();
        $this->assertArrayHasKey('ShipmentId',$o);
        $this->assertEquals('77', $o['ShipmentId']);
    }
    
    public function testSetUseToken(){
        $this->assertNull($this->object->setUseToken());
        $this->assertNull($this->object->setUseToken(true));
        $this->assertNull($this->object->setUseToken(false));
        $this->assertFalse($this->object->setUseToken('wrong'));
    }
    
    public function testShipmentId(){
        $this->assertFalse($this->object->setShipmentId(null)); //can't be nothing
        $this->assertFalse($this->object->setShipmentId(5)); //can't be an int
        $this->assertNull($this->object->setShipmentId('123456'));
        $o = $this->object->getOptions();
        $this->assertArrayHasKey('ShipmentId',$o);
        $this->assertEquals('123456',$o['ShipmentId']);
    }
    
    /**
    * @return array
    */
    public function timeProvider() {
        return array(
            array(null, null), //nothing given, so no change
            array(true, true), //not strings or numbers
            array('', ''), //strings, but empty
            array('-1 min', null), //one set
            array(null, '-1 min'), //other set
            array('-1 min', '-1 min'), //both set
        );
    }
    
    /**
     * @dataProvider timeProvider
     */
    public function testSetTimeLimits($a, $b){
        $this->object->setTimeLimits($a,$b);
        $o = $this->object->getOptions();
        $this->assertArrayHasKey('LastUpdatedAfter',$o);
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i',$o['LastUpdatedAfter']);
        $this->assertArrayHasKey('LastUpdatedBefore',$o);
        $this->assertStringMatchesFormat('%d-%d-%dT%d:%d:%d%i',$o['LastUpdatedBefore']);
    }
    
    public function testResetTimeLimit(){
        $this->object->setTimeLimits('-1 min','-1 min');
        $o = $this->object->getOptions();
        $this->assertArrayHasKey('LastUpdatedAfter',$o);
        $this->assertArrayHasKey('LastUpdatedBefore',$o);
        
        $this->object->resetTimeLimits();
        $check = $this->object->getOptions();
        $this->assertArrayNotHasKey('LastUpdatedAfter',$check);
        $this->assertArrayNotHasKey('LastUpdatedBefore',$check);
    }
    
    public function testFetchItems(){
        resetLog();
        $this->object->setMock(true,'fetchShipmentItems.xml'); //no token
        $this->assertFalse($this->object->fetchItems()); //no ID set yet
        
        $this->object->setShipmentId('123');
        $this->assertNull($this->object->fetchItems());
        
        $o = $this->object->getOptions();
        $this->assertEquals('ListInboundShipmentItems',$o['Action']);
        
        $check = parseLog();
        $this->assertEquals('Single Mock File set: fetchShipmentItems.xml',$check[1]);
        $this->assertEquals('Shipment ID must be set before requesting items!',$check[2]);
        $this->assertEquals('Fetched Mock File: mock/fetchShipmentItems.xml',$check[3]);
        
        $this->assertFalse($this->object->hasToken());
        
        return $this->object;
    }
    
    public function testFetchItemsToken1(){
        resetLog();
        $this->object->setMock(true,'fetchShipmentItemsToken.xml'); //no token
        
        //without using token
        $this->object->setShipmentId('123');
        $this->assertNull($this->object->fetchItems());
        $check = parseLog();
        $this->assertEquals('Single Mock File set: fetchShipmentItemsToken.xml',$check[1]);
        $this->assertEquals('Fetched Mock File: mock/fetchShipmentItemsToken.xml',$check[2]);
        
        $this->assertTrue($this->object->hasToken());
        $o = $this->object->getOptions();
        $this->assertEquals('ListInboundShipmentItems',$o['Action']);
        $r = $this->object->getItems();
        $this->assertArrayHasKey(0,$r);
        $this->assertEquals(1,count($r));
        $this->assertInternalType('array',$r[0]);
        $this->assertArrayNotHasKey(1,$r);
    }
    
    public function testFetchShipmentsToken2(){
        resetLog();
        $this->object->setMock(true,array('fetchShipmentItemsToken.xml','fetchShipmentItemsToken2.xml'));
        
        //with using token
        $this->object->setUseToken();
        $this->object->setShipmentId('123');
        $this->assertNull($this->object->fetchItems());
        $check = parseLog();
        $this->assertEquals('Mock files array set.',$check[1]);
        $this->assertEquals('Fetched Mock File: mock/fetchShipmentItemsToken.xml',$check[2]);
        $this->assertEquals('Recursively fetching more shipment items',$check[3]);
        $this->assertEquals('Fetched Mock File: mock/fetchShipmentItemsToken2.xml',$check[4]);
        $this->assertFalse($this->object->hasToken());
        $o = $this->object->getOptions();
        $this->assertEquals('ListInboundShipmentItemsByNextToken',$o['Action']);
        $r = $this->object->getItems();
        $this->assertArrayHasKey(0,$r);
        $this->assertArrayHasKey(1,$r);
        $this->assertEquals(2,count($r));
        $this->assertInternalType('array',$r[0]);
        $this->assertInternalType('array',$r[1]);
        $this->assertNotEquals($r[0],$r[1]);
    }
    
    /**
     * @depends testFetchItems
     */
    public function testGetShipmentId($o){
        $this->assertEquals('SSF85DGIZZ3OF1',$o->getShipmentId(0));
        
        $this->assertFalse($o->getShipmentId('wrong')); //not number
        $this->assertFalse($o->getShipmentId(1.5)); //no decimals
        $this->assertFalse($this->object->getShipmentId()); //not fetched yet for this object
    }
    
    /**
     * @depends testFetchItems
     */
    public function testGetSellerSKU($o){
        $this->assertEquals('SampleSKU1',$o->getSellerSKU(0));
        
        $this->assertFalse($o->getSellerSKU('wrong')); //not number
        $this->assertFalse($o->getSellerSKU(1.5)); //no decimals
        $this->assertFalse($this->object->getSellerSKU()); //not fetched yet for this object
    }
    
    /**
     * @depends testFetchItems
     */
    public function testGetFulfillmentNetworkSKU($o){
        $this->assertEquals('B000FADVPQ',$o->getFulfillmentNetworkSKU(0));
        
        $this->assertFalse($o->getFulfillmentNetworkSKU('wrong')); //not number
        $this->assertFalse($o->getFulfillmentNetworkSKU(1.5)); //no decimals
        $this->assertFalse($this->object->getFulfillmentNetworkSKU()); //not fetched yet for this object
    }
    
    /**
     * @depends testFetchItems
     */
    public function testGetQuantityShipped($o){
        $this->assertEquals('3',$o->getQuantityShipped(0));
        
        $this->assertFalse($o->getQuantityShipped('wrong')); //not number
        $this->assertFalse($o->getQuantityShipped(1.5)); //no decimals
        $this->assertFalse($this->object->getQuantityShipped()); //not fetched yet for this object
    }
    
    /**
     * @depends testFetchItems
     */
    public function testGetQuantityReceived($o){
        $this->assertEquals('0',$o->getQuantityReceived(0));
        
        $this->assertFalse($o->getQuantityReceived('wrong')); //not number
        $this->assertFalse($o->getQuantityReceived(1.5)); //no decimals
        $this->assertFalse($this->object->getQuantityReceived()); //not fetched yet for this object
    }
    
    /**
     * @depends testFetchItems
     */
    public function testGetQuantityInCase($o){
        $this->assertEquals('0',$o->getQuantityInCase(0));
        
        $this->assertFalse($o->getQuantityInCase('wrong')); //not number
        $this->assertFalse($o->getQuantityInCase(1.5)); //no decimals
        $this->assertFalse($this->object->getQuantityInCase()); //not fetched yet for this object
    }
    
    /**
     * @depends testFetchItems
     */
    public function testGetItems($o){
        $shipment = $o->getItems(0);
        $this->assertInternalType('array',$shipment);
        
        $list = $o->getItems(null);
        $this->assertInternalType('array',$list);
        $this->assertArrayHasKey(0,$list);
        $this->assertArrayHasKey(1,$list);
        $this->assertEquals($shipment,$list[0]);
        
        $default = $o->getItems();
        $this->assertEquals($list,$default);
        
        
        
        $x = array();
        $x1 = array();
        $x1['ShipmentId'] = 'SSF85DGIZZ3OF1';
        $x1['SellerSKU'] = 'SampleSKU1';
        $x1['QuantityShipped'] = '3';
        $x1['QuantityInCase'] = '0';
        $x1['QuantityReceived'] = '0';
        $x1['FulfillmentNetworkSKU'] = 'B000FADVPQ';
        $x[0] = $x1;
        $x2 = array();
        $x2['ShipmentId'] = 'SSF85DGIZZ3OF1';
        $x2['SellerSKU'] = 'SampleSKU2';
        $x2['QuantityShipped'] = '10';
        $x2['QuantityInCase'] = '0';
        $x2['QuantityReceived'] = '0';
        $x2['FulfillmentNetworkSKU'] = 'B0011VECH4';
        $x[1] = $x2;
        
        $this->assertEquals($x, $list);
        
        $this->assertFalse($this->object->getItems()); //not fetched yet for this object
    }
    
}

require_once('helperFunctions.php');