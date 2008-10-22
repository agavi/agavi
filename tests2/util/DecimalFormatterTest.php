<?php

class DecimalFormatterTest extends AgaviTestCase
{
	public function testFormatNumber()
	{
		$df = new AgaviDecimalFormatter('0.00');

		$this->assertEquals('5345.50', $df->formatNumber(5345.502));
		// test rounding
		$this->assertEquals('5345.51', $df->formatNumber(5345.505));
		$this->assertEquals('10000.00', $df->formatNumber(9999.995));


		$df->setFormat('#.##');
		$this->assertEquals('0', $df->formatNumber(0));
		$this->assertEquals('0.345', $df->formatNumber(0.345));
		$this->assertEquals('1345', $df->formatNumber(1345));

// should this be supported ? currently isn't
//		$df->setFormat('.##');
//		$this->assertEquals('.345', $df->formatNumber(0.345));

		$df->setFormat(',###.##');
		$this->assertEquals('12,345,678', $df->formatNumber(12345678));
		$this->assertEquals('12,345,678.09', $df->formatNumber('12345678.09'));

		$df->setFormat('00;#-');
		$this->assertEquals('05', $df->formatNumber(5));
		$this->assertEquals('05-', $df->formatNumber(-5));

		$df->setFormat('00##');
		$this->assertEquals('0015', $df->formatNumber(15));
		$this->assertEquals('-0015', $df->formatNumber(-15));

		// example from prado manual (we want to be compatible, don't we ? :)
		$df->setFormat('##,###.00');
		$this->assertEquals('1,234,567.12', $df->formatNumber(1234567.12345));

		$df->setFormat('##,###.##');
		$this->assertEquals('1,234,567.12345', $df->formatNumber(1234567.12345));

		$df->setFormat('##,##.0000');
		$this->assertEquals('1,23,45,67.1235', $df->formatNumber(1234567.12345));

		$df->setFormat('#,##,##0');
		$this->assertEquals('12,34,56,789', $df->formatNumber(123456789.0));

		$df->setFormat('#,#,###,##.0');
		$this->assertEquals('1,234,567,89.1', $df->formatNumber(123456789.12345));


		$df->setFormat('000,000,000.0');
		$this->assertEquals('001,234,567.1', $df->formatNumber(1234567.12345));
	}
}

?>