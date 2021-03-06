<?php
/**
 * @group Db_TablesModel
 */
class Kwf_Db_TablesModel_Test extends Kwf_Test_TestCase
{
    public function setUp()
    {
        $this->_db = $this->getMock('Kwf_Model_Db_TestAdapter',
            array('fetchCol', 'query'));
        $this->_db->expects($this->once())
            ->method('fetchCol')
            ->with($this->equalTo('SHOW TABLES'))
            ->will($this->returnValue(array('foo', 'bar', 'bam')));
        $this->_model = new Kwf_Db_TablesModel(array(
            'db' => $this->_db
        ));
        parent::setUp();
    }

    public function testGetAll()
    {
        $rows = $this->_model->getRows();
        $this->assertEquals(3, count($rows));
        $this->assertEquals('foo', $rows->current()->table);
    }

    public function testGetById()
    {
        $row = $this->_model->getRow('bar');
        $this->assertNotNull($row);
        $this->assertEquals('bar', $row->table);
    }

    public function testDropTable()
    {
        $this->_db->expects($this->once())
            ->method('query')
            ->with($this->equalTo('DROP TABLE bar'));
        $row = $this->_model->getRow('bar');
        $row->delete();
    }
}
