<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2016 Richard Fussenegger
 * @license MIT
 */

namespace Fleshgrinder\Network;

/**
 * @coversDefaultClass Fleshgrinder\Network\Ipv6Address
 */
final class Ipv6AddressTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @covers ::__construct
	 * @covers ::fromArray
	 */
	public function testFromArray() {
		$expected = new Ipv6Address(0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff);
		$actual = Ipv6Address::fromArray([0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff]);

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::__construct
	 * @covers ::fromArray
	 * @covers ::fromBinary
	 * @covers ::fromInteger
	 */
	public function testFromIntegerLow() {
		$expected = new Ipv6Address(0, 0, 0, 0, 0, 0, 0, 0);
		$actual = Ipv6Address::fromInteger(0);

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::__construct
	 * @covers ::fromArray
	 * @covers ::fromBinary
	 * @covers ::fromInteger
	 */
	public function testFromIntegerHigh() {
		$expected = new Ipv6Address(0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff, 0xffff);
		$actual = Ipv6Address::fromInteger('340282366920938463463374607431768211455');

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::__construct
	 * @covers ::__toString
	 * @covers ::jsonSerialize
	 * @covers ::toBinary
	 */
	public function testJsonSerialize() {
		$this->assertSame('"::1"', json_encode(new Ipv6Address(0, 0, 0, 0, 0, 0, 0, 1)));
	}

}
