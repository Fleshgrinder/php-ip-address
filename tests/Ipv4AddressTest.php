<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2016 Richard Fussenegger
 * @license MIT
 */

namespace Fleshgrinder\Network;

/**
 * @coversDefaultClass Fleshgrinder\Network\Ipv4Address
 */
final class Ipv4AddressTest extends \PHPUnit_Framework_TestCase {

	public function dataProviderInvalidOctetType() {
		return [
			'float' => ['double', 1.0],
			'object' => ['stdClass', (object) []],
			'string' => ['string', 'foo'],
		];
	}

	/**
	 * @covers ::__construct
	 * @dataProvider dataProviderInvalidOctetType
	 */
	public function testConstructOctetTypeCheck($type, $d) {
		try {
			new Ipv4Address(127, 0, 0, $d);
		}
		catch (InvalidIpAddressException $e) {
			$this->assertSame("Byte must be a valid integer, got {$type}.", $e->getMessage());
		}
	}

	public function dataProviderInvalidOctetRange() {
		return [
			'low' => [-1],
			'high' => [256],
		];
	}

	/**
	 * @covers ::__construct
	 * @dataProvider \dataProviderInvalidOctetRange
	 */
	public function testConstructOctetRangeCheck($d) {
		try {
			new Ipv4Address(127, 0, 0, $d);
		}
		catch (InvalidIpAddressException $e) {
			$this->assertSame("Byte must be between 0 and 255, got {$d}.", $e->getMessage());
		}
	}

	public function dataProviderArrayBoundaries() {
		return [
			'low' => [3, [0, 0, 0]],
			'high' => [5, [0, 0, 0, 0, 0]],
		];
	}

	/**
	 * @covers ::fromArray
	 * @dataProvider dataProviderArrayBoundaries
	 */
	public function testFromArrayBoundaries($c, $data) {
		try {
			Ipv4Address::fromArray($data);
		}
		catch (InvalidIpAddressException $e) {
			$this->assertSame("Invalid array element count, expected 4 got {$c}.", $e->getMessage());
		}
	}

	/**
	 * @covers ::__construct
	 * @covers ::__set_state
	 */
	public function testSetState() {
		$expected = new Ipv4Address(127, 0, 0, 1);
		$actual = eval('return ' . var_export($expected, true) . ';');

		$this->assertNotSame($expected, $actual);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @covers ::__construct
	 * @covers ::equals
	 */
	public function testEqualsReflexiveness() {
		$x = new Ipv4Address(127, 0, 0, 1);

		$this->assertTrue($x->equals($x));
	}

	/**
	 * @covers ::__construct
	 * @covers ::equals
	 */
	public function testEqualsSymmetry() {
		$x = new Ipv4Address(127, 0, 0, 1);
		$y = new Ipv4Address(127, 0, 0, 1);

		$this->assertTrue($y->equals($x));
		$this->assertTrue($x->equals($y));
	}

	/**
	 * @covers ::__construct
	 * @covers ::equals
	 */
	public function testEqualsAsymmetry() {
		$x = new Ipv4Address(127, 0, 0, 0);
		$y = new Ipv4Address(127, 0, 0, 1);

		$this->assertFalse($x->equals($y));
		$this->assertFalse($y->equals($x));
	}

	/**
	 * @covers ::__construct
	 * @covers ::equals
	 */
	public function testEqualsTransitiveness() {
		$x = new Ipv4Address(127, 0, 0, 1);
		$y = new Ipv4Address(127, 0, 0, 1);
		$z = new Ipv4Address(127, 0, 0, 1);

		$this->assertTrue($x->equals($y));
		$this->assertTrue($y->equals($z));
		$this->assertTrue($x->equals($z));
	}

	/**
	 * @covers ::__construct
	 * @covers ::equals
	 */
	public function testEqualsNull() {
		$this->assertFalse((new Ipv4Address(127, 0, 0, 1))->equals(null));
	}

	/**
	 * @covers ::__construct
	 * @covers ::getIterator
	 */
	public function testGetIterator() {
		$this->assertSame([127, 0, 0, 1], iterator_to_array(new Ipv4Address(127, 0, 0, 1)));
	}

	/**
	 * @covers ::__construct
	 * @covers ::isBroadcast
	 */
	public function testIsBroadcast() {
		$this->assertTrue((new Ipv4Address(255, 255, 255, 255))->isBroadcast());
	}

	public function dataProviderDocumentation() {
		return [
			'TEST-NET-1 low' => [192, 0, 2, 0],
			'TEST-NET-1 high' => [192, 0, 2, 255],

			'TEST-NET-2 low' => [198, 51, 100, 0],
			'TEST-NET-2 high' => [198, 51, 100, 255],

			'TEST-NET-3 low' => [203, 0, 113, 0],
			'TEST-NET-3 high' => [203, 0, 113, 255],
		];
	}

	/**
	 * @covers ::__construct
	 * @covers ::isDocumentation
	 * @dataProvider dataProviderDocumentation
	 */
	public function testIsDocumentation($a, $b, $c, $d) {
		$this->assertTrue((new Ipv4Address($a, $b, $c, $d))->isDocumentation());
	}

	/**
	 * @covers ::__construct
	 * @covers ::isGlobal
	 * @covers ::isBroadcast
	 * @covers ::isDocumentation
	 * @covers ::isLinkLocal
	 * @covers ::isLoopback
	 * @covers ::isPrivate
	 * @covers ::isUnspecified
	 */
	public function testIsGlobal() {
		// Google bot IP address.
		$this->assertTrue((new Ipv4Address(64, 233, 160, 0))->isGlobal());
	}

	/**
	 * @covers ::__construct
	 * @covers ::isLinkLocal
	 */
	public function testIsLinkLocalLow() {
		$this->assertTrue((new Ipv4Address(192, 254, 0, 0))->isLinkLocal());
	}

	/**
	 * @covers ::__construct
	 * @covers ::isLinkLocal
	 */
	public function testIsLinkLocalHigh() {
		$this->assertTrue((new Ipv4Address(192, 254, 255, 255))->isLinkLocal());
	}

	/**
	 * @covers ::__construct
	 * @covers ::isLoopback
	 */
	public function testIsLoopbackLow() {
		$this->assertTrue((new Ipv4Address(127, 0, 0, 0))->isLoopback());
	}

	/**
	 * @covers ::__construct
	 * @covers ::isLoopback
	 */
	public function testIsLoopbackHigh() {
		$this->assertTrue((new Ipv4Address(127, 255, 255, 255))->isLoopback());
	}

	public function dataProviderIsPrivate() {
		return [
			'10 low' => [10, 0, 0, 0],
			'10 high' => [10, 255, 255, 255],
		];
	}

	/**
	 * @covers ::__construct
	 * @covers ::isPrivate
	 * @dataProvider dataProviderIsPrivate
	 */
	public function testIsPrivate($a, $b, $c, $d) {
		$this->assertTrue((new Ipv4Address($a, $b, $c, $d))->isPrivate());
	}

	/**
	 * @covers ::__construct
	 * @covers ::__toString
	 * @covers ::jsonSerialize
	 */
	public function testJsonSerialize() {
		$this->assertSame('"127.0.0.1"', json_encode(new Ipv4Address(127, 0, 0, 1)));
	}

	/**
	 * @covers ::__construct
	 * @covers ::__toString
	 * @covers ::toInteger
	 */
	public function testToInteger() {
		$expected = ip2long('127.0.0.1');
		$actual = (new Ipv4Address(127, 0, 0, 1))->toInteger();

		$this->assertSame($expected, $actual);
	}

}
