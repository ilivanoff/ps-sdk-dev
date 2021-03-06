<?php

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-01-25 at 14:20:52.
 */
class ArrayAdapterTest extends BasePsTest {

    /**
     * @covers ArrayAdapter::inst
     * @covers ArrayAdapter::getData
     * @covers ArrayAdapter::remove
     * @covers ArrayAdapter::set
     * @covers ArrayAdapter::restoreStory
     */
    public function testInstByRef() {
        $data = array(
            'a' => 1,
            'b' => 2,
            'c' => 3
        );
        $dataNew = $data;

        $byRef = true;
        $editable = true;
        $aa = ArrayAdapter::inst($data, $byRef, $editable);

        $this->assertEquals($aa->getData(), $data);

        // SET

        $aa->set('d', 4);
        $dataNew['d'] = 4;
        $this->assertEquals($data, $dataNew);
        $this->assertEquals($aa->getData(), $dataNew);

        $aa->set('e', 5, true);
        $dataNew['e'] = 5;
        $this->assertEquals($data, $dataNew);
        $this->assertEquals($aa->getData(), $dataNew);

        $aa->restoreStory();
        unset($dataNew['e']);
        $this->assertEquals($data, $dataNew);
        $this->assertEquals($aa->getData(), $dataNew);

        $aa->set('e', 6);
        $aa->set('e', 7, true);
        $aa->set('e', 8, true);
        $aa->restoreStory();
        $dataNew['e'] = 6;
        $this->assertEquals($data, $dataNew);
        $this->assertEquals($aa->getData(), $dataNew);

        //REMOVE
        $aa->remove('e');
        unset($dataNew['e']);
        $this->assertEquals($data, $dataNew);
        $this->assertEquals($aa->getData(), $dataNew);

        $aa->remove(array('c', 'd'));
        unset($dataNew['c']);
        unset($dataNew['d']);
        $this->assertEquals($data, $dataNew);
        $this->assertEquals($aa->getData(), $dataNew);
    }

    /**
     * @covers ArrayAdapter::inst
     * @covers ArrayAdapter::getData
     * @covers ArrayAdapter::remove
     * @covers ArrayAdapter::set
     * @covers ArrayAdapter::restoreStory
     */
    public function testInstByValue() {
        $data = array(
            'a' => 1,
            'b' => 2,
            'c' => 3
        );
        $dataStore = $data;

        $byRef = false;
        $editable = true;
        $aa = ArrayAdapter::inst($data, $byRef, $editable);

        $this->assertEquals($aa->getData(), $data);

        // SET

        $aa->set('d', 4);
        $this->assertEquals($data, $dataStore);

        $aa->set('e', 5, true);
        $this->assertEquals($data, $dataStore);

        $aa->restoreStory();
        $this->assertEquals($data, $dataStore);

        $aa->set('e', 6);
        $aa->set('e', 7, true);
        $aa->set('e', 8, true);
        $aa->restoreStory();
        $this->assertEquals($data, $dataStore);

        //REMOVE
        $aa->remove('e');
        $this->assertEquals($data, $dataStore);

        $aa->remove(array('c', 'd'));
        $this->assertEquals($data, $dataStore);
    }

    /**
     * @covers ArrayAdapter::hasOneOf
     */
    public function testHasOneOf() {
        $data = array('a' => 1, 'b' => 2, 'c' => 3);
        $aa = ArrayAdapter::inst($data);

        $this->assertTrue($aa->hasOneOf(array('a')));
        $this->assertTrue($aa->hasOneOf(array('b')));
        $this->assertTrue($aa->hasOneOf(array('c')));
        $this->assertTrue($aa->hasOneOf(array('x', 'a')));
        $this->assertFalse($aa->hasOneOf(array('d')));
    }

    /**
     * @covers ArrayAdapter::hasAll
     */
    public function testHasAll() {
        $data = array('a' => 1, 'b' => 2, 'c' => 3);
        $aa = ArrayAdapter::inst($data);

        $this->assertTrue($aa->hasAll(array('a', 'b', 'c')));
        $this->assertTrue($aa->hasAll(array('c', 'a', 'b')));
        $this->assertTrue($aa->hasAll(array('b')));
        $this->assertTrue($aa->hasAll(array('c')));
        $this->assertFalse($aa->hasAll(array('x', 'a')));
        $this->assertFalse($aa->hasAll(array('d')));
        $this->assertFalse($aa->hasAll(array('c', 'a', 'x', 'b')));
    }

    /**
     * @covers ArrayAdapter::leaveKeys
     * @todo   Implement testLeaveKeys().
     */
    public function testLeaveKeys() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::hasAllNoEmpty
     * @todo   Implement testHasAllNoEmpty().
     */
    public function testHasAllNoEmpty() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::getFirstEmpty
     * @todo   Implement testGetFirstEmpty().
     */
    public function testGetFirstEmpty() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::has
     * @todo   Implement testHas().
     */
    public function testHas() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::hasNoEmpty
     * @todo   Implement testHasNoEmpty().
     */
    public function testHasNoEmpty() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::get
     * @todo   Implement testGet().
     */
    public function testGet() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::int
     * @todo   Implement testInt().
     */
    public function testInt() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::str
     * @todo   Implement testStr().
     */
    public function testStr() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::bool
     * @todo   Implement testBool().
     */
    public function testBool() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::arr
     * @todo   Implement testArr().
     */
    public function testArr() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::getByKeyPrefix
     * @todo   Implement testGetByKeyPrefix().
     */
    public function testGetByKeyPrefix() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::hasOneOfValue
     * @todo   Implement testHasOneOfValue().
     */
    public function testHasOneOfValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::hasAllValue
     * @todo   Implement testHasAllValue().
     */
    public function testHasAllValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::hasValue
     * @todo   Implement testHasValue().
     */
    public function testHasValue() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::dataToString
     * @todo   Implement testDataToString().
     */
    public function testDataToString() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::__toString
     * @todo   Implement test__toString().
     */
    public function test__toString() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

    /**
     * @covers ArrayAdapter::copy
     * @todo   Implement testCopy().
     */
    public function testCopy() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}