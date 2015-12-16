<?php

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-01-20 at 15:13:22.
 */
class PSDBTest extends BasePsTest {

    /**
     * При подготовке тестов подключимся к тестовой схеме SDK
     */
    public static function setUpBeforeClass() {
        PsConnectionPool::configure(PsConnectionParams::sdkTest());
    }

    /**
     * @covers PSDB::insert
     */
    public function testInsert() {
        $table = 'ps_test_data_load';
        PSDB::update("delete from $table");

        $this->assertEquals(PSDB::getRec("select count(1) as cnt from $table"), array('cnt' => 0));

        PSDB::insert("insert into $table (v_key, v_value) values (?, ?)", array('key1', 'val1'));
        $rec = PSDB::getRec("select v_key, v_value from $table");
        $this->assertEquals($rec, array('v_key' => 'key1', 'v_value' => 'val1'));

        PSDB::insert("insert into $table (v_key, v_value) values (?, ?)", array('key2', 'val2'));
        $rec = PSDB::getRec("select v_key, v_value from $table where v_key=?", 'key2');
        $this->assertEquals($rec, array('v_key' => 'key2', 'v_value' => 'val2'));

        try {
            PSDB::insert("insert into $table (v_key, v_value) values (?, ?)", array('key1', 'val1'));
            $this->brakeNoException();
        } catch (Exception $ex) {
            $this->assertTrue($ex instanceof DBException);
            $this->assertEquals($ex->getCode(), DBException::ERROR_DUPLICATE_ENTRY);
        }
    }

    /**
     * @covers PSDB::update
     */
    public function testUpdate() {
        $table = 'ps_test_data_load';
        PsDatabaseTestsHelper::ps_test_data_load_Fill(1, 2);

        $updated = PSDB::update("update $table set v_value=? where v_key=?", array('val3', 'key3'));
        $this->assertEquals($updated, 0);

        $updated = PSDB::update("update $table set v_value=? where v_key=?", array('val1-new', 'key1'));
        $this->assertEquals($updated, 1);

        $rec = PSDB::getRec("select v_key, v_value from $table where v_key=?", array('key1'), true);
        $this->assertEquals($rec, array('v_key' => 'key1', 'v_value' => 'val1-new'));

        $updated = PSDB::update("update $table set v_value=?", 'val-new');
        $this->assertEquals($updated, 2);

        $rec = PSDB::getRec("select v_key, v_value from $table where v_key=?", array('key1'), true);
        $this->assertEquals($rec, array('v_key' => 'key1', 'v_value' => 'val-new'));

        try {
            PSDB::update("update $table set v_key=?", 'key-new');
            $this->brakeNoException();
        } catch (Exception $ex) {
            $this->assertTrue($ex instanceof DBException);
            $this->assertEquals($ex->getCode(), DBException::ERROR_DUPLICATE_ENTRY);
        }
    }

    /**
     * @covers PSDB::getRec
     */
    public function testGetRec() {
        $table = 'ps_test_data_load';
        PsDatabaseTestsHelper::ps_test_data_load_Fill(1, 2);

        $rec = PSDB::getRec("select v_key, v_value from $table where v_key=?", 'key1', true);
        $this->assertEquals($rec, array('v_key' => 'key1', 'v_value' => 'val1'));

        $rec = PSDB::getRec("select v_key, v_value from $table where v_key=?", array('key1'), true);
        $this->assertEquals($rec, array('v_key' => 'key1', 'v_value' => 'val1'));

        $rec = PSDB::getRec("select v_key, v_value from $table where v_key=?", 'key2', true);
        $this->assertEquals($rec, array('v_key' => 'key2', 'v_value' => 'val2'));

        $rec = PSDB::getRec("select v_key, v_value from $table where v_key=?", array('key2'), true);
        $this->assertEquals($rec, array('v_key' => 'key2', 'v_value' => 'val2'));

        $rec = PSDB::getRec("select v_key, v_value from $table where v_key=?", 'key3', false);
        $this->assertNull($rec);

        try {
            PSDB::getRec("select v_key, v_value from $table where v_key=?", 'key3', true);
            $this->brakeNoException();
        } catch (Exception $ex) {
            $this->assertTrue($ex instanceof DBException);
            $this->assertEquals($ex->getCode(), DBException::ERROR_NO_DATA_FOUND);
        }

        try {
            PSDB::getRec("select v_key, v_value from $table");
            $this->brakeNoException();
        } catch (Exception $ex) {
            $this->assertTrue($ex instanceof DBException);
            $this->assertEquals($ex->getCode(), DBException::ERROR_TOO_MANY_ROWS);
        }
    }

    /**
     * @covers PSDB::getArray
     */
    public function testGetArray() {
        $table = 'ps_test_data_load';
        PsDatabaseTestsHelper::ps_test_data_load_Fill(1, 2);

        $rec = PSDB::getArray("select v_key, v_value from $table where v_key=?", 'key1');
        $this->assertEquals($rec, array(array('v_key' => 'key1', 'v_value' => 'val1')));

        $rec = PSDB::getArray("select v_key, v_value from $table where v_key=?", 'key2');
        $this->assertEquals($rec, array(array('v_key' => 'key2', 'v_value' => 'val2')));

        $rec = PSDB::getArray("select v_key, v_value from $table order by v_key");
        $this->assertEquals($rec, array(array('v_key' => 'key1', 'v_value' => 'val1'), array('v_key' => 'key2', 'v_value' => 'val2')));

        $rec = PSDB::getArray("select v_key, v_value from $table order by v_key");
        $this->assertEquals($rec, array(array('v_key' => 'key1', 'v_value' => 'val1'), array('v_key' => 'key2', 'v_value' => 'val2')));
    }

    /**
     * @covers PSDB::getEmptyRec
     * @todo   Implement testGetEmptyRec().
     */
    public function testGetEmptyRec() {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
                'This test has not been implemented yet.'
        );
    }

}