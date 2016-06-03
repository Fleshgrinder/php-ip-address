<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2016 Richard Fussenegger
 * @license MIT
 */

namespace Fleshgrinder\Network;

/**
 * @coversDefaultClass Fleshgrinder\Network\IpAddress
 */
final class IpAddressTest extends \PHPUnit_Framework_TestCase {

	public function dataProviderArrayBoundaries() {
		$data = [];

		foreach ([3, 5, 7, 9] as $i) {
			$data[(string) $i] = [$i, array_fill(0, $i, 0)];
		}

		return $data;
	}

	/**
	 * @covers ::fromArray
	 * @dataProvider dataProviderArrayBoundaries
	 */
	public function testFromArrayBoundaries($c, $data) {
		try {
			IpAddress::fromArray($data);
		}
		catch (InvalidIpAddressException $e) {
			$this->assertSame("Invalid array element count, expected 4, 8 or 16 got {$c}.", $e->getMessage());
		}
	}

	/**
	 * @covers ::fromArray
	 * @covers Fleshgrinder\Network\Ipv4Address::__construct
	 * @covers Fleshgrinder\Network\Ipv4Address::fromArray
	 */
	public function testFromIpv4Array() {
		$expected = new Ipv4Address(127, 0, 0, 1);
		$actual = IpAddress::fromArray([127, 0, 0, 1]);

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::fromArray
	 * @covers Fleshgrinder\Network\Ipv6Address::__construct
	 * @covers Fleshgrinder\Network\Ipv6Address::fromArray
	 */
	public function testFromIpv6Array() {
		$expected = new Ipv6Address(0, 0, 0, 0, 0, 0, 0, 1);
		$actual = IpAddress::fromArray([0, 0, 0, 0, 0, 0, 0, 1]);

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	public function dataProviderBinaryBoundaries() {
		$data = [];

		foreach ([3, 5, 15, 17] as $i) {
			$data[(string) $i] = [$i, str_repeat('0', $i)];
		}

		return $data;
	}

	/**
	 * @covers ::fromBinary
	 * @dataProvider dataProviderBinaryBoundaries
	 */
	public function testFromBinaryEmpty($bytes, $data) {
		try {
			IpAddress::fromBinary($data);
		}
		catch (InvalidIpAddressException $e) {
			$this->assertSame("Invalid byte count, expected 4 or 16 got {$bytes}.", $e->getMessage());
		}
	}

	/**
	 * @covers ::fromBinary
	 * @covers Fleshgrinder\Network\Ipv4Address::__construct
	 * @covers Fleshgrinder\Network\Ipv4Address::fromArray
	 * @covers Fleshgrinder\Network\Ipv4Address::fromBinary
	 * @covers Fleshgrinder\Network\Ipv4Address::fromString
	 */
	public function testFromBinaryIpv4() {
		$expected = new Ipv4Address(127, 0, 0, 1);
		$actual = IpAddress::fromBinary(inet_pton('127.0.0.1'));

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::fromBinary
	 * @covers Fleshgrinder\Network\Ipv6Address::__construct
	 * @covers Fleshgrinder\Network\Ipv6Address::fromArray
	 * @covers Fleshgrinder\Network\Ipv6Address::fromBinary
	 */
	public function testFromBinaryIpv6() {
		$expected = new Ipv6Address(0, 0, 0, 0, 0, 0, 0, 1);
		$actual = IpAddress::fromBinary(inet_pton('::1'));

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::fromString
	 * @expectedException Fleshgrinder\Network\InvalidIpAddressException
	 * @expectedExceptionMessage Unrecognized address ''.
	 */
	public function testFromStringEmpty() {
		IpAddress::fromString('');
	}

	/**
	 * @covers ::fromString
	 * @covers Fleshgrinder\Network\Ipv4Address::__construct
	 * @covers Fleshgrinder\Network\Ipv4Address::fromArray
	 * @covers Fleshgrinder\Network\Ipv4Address::fromString
	 */
	public function testFromIpv4String() {
		$expected = new Ipv4Address(127, 0, 0, 1);
		$actual = IpAddress::fromString('127.0.0.1');

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::fromString
	 * @covers Fleshgrinder\Network\Ipv6Address::__construct
	 * @covers Fleshgrinder\Network\Ipv6Address::fromArray
	 * @covers Fleshgrinder\Network\Ipv6Address::fromBinary
	 * @covers Fleshgrinder\Network\Ipv6Address::fromString
	 */
	public function testFromIpv6String() {
		$expected = new Ipv6Address(0, 0, 0, 0, 0, 0, 0, 1);
		$actual = IpAddress::fromString('0:0:0:0:0:0:0:1');

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

}
