<?php
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2016 Richard Fussenegger
 * @license MIT
 */

namespace Fleshgrinder\Network;

use Variable;

/**
 * Immutable value object that represents an arbitrary but valid Internet Protocol ip_address in version 6.
 */
final class Ipv6Address extends IpAddress {

	/**
	 * @var int[]
	 */
	private $segments;

	/**
	 * Construct new IPv6 ip_address instance.
	 *
	 * @throws InvalidIpAddressException
	 */
	public function __construct(int $a, int $b, int $c, int $d, int $e, int $f, int $g, int $h) {
		$segments = [$a, $b, $c, $d, $e, $f, $g, $h];

		foreach ($segments as &$segment) {
			if ($segment < 0 || $segment > 0xffff) {
				throw new InvalidIpAddressException("Segment must be between 0 and 65535, got {$segment}");
			}
		}

		$this->segments = $segments;
	}

	public static function __set_state(array $data): Ipv6Address {
		assert(isset($data['segments']));
		assert(count($data['segments']) === 8);
		assert(Variable::isStrictArray($data['segments']));

		return new self(...$data['segments']);
	}

	/**
	 * @see Fleshgrinder\Network\Ipv6Address::getSegments()
	 * @see Fleshgrinder\Network\Ipv6Address::toArray()
	 * @param int[] $bytes
	 * @return Ipv6Address
	 * @throws InvalidIpAddressException
	 */
	public static function fromArray(array $bytes): Ipv6Address {
		assert('\Variable::isStrictArray($bytes)', 'bytes must be a strict array');
		assert('\Variable::hasScalarNaturalNumbersOnly($bytes)', 'bytes must contain scalar natural numbers only');

		$c = count($bytes);

		if ($c === 8) {
			/** @noinspection PhpParamsInspection */
			return new self(...$bytes);
		}

		if ($c === 16) {
			return new self(
				$bytes[0] << 8 | $bytes[1],
				$bytes[2] << 8 | $bytes[3],
				$bytes[4] << 8 | $bytes[5],
				$bytes[6] << 8 | $bytes[7],
				$bytes[8] << 8 | $bytes[9],
				$bytes[10] << 8 | $bytes[11],
				$bytes[12] << 8 | $bytes[13],
				$bytes[14] << 8 | $bytes[15]
			);
		}

		throw new InvalidIpAddressException("Invalid array element count, expected 8 or 16 got {$c}");
	}

	/**
	 * @param $in_addr
	 * @return Ipv6Address
	 * @throws InvalidIpAddressException
	 */
	public static function fromBinary(string $in_addr): Ipv6Address {
		$address = inet_ntop($in_addr);

		if ($address === false) {
			throw new InvalidIpAddressException('Invalid in_addr value');
		}

		$hex = bin2hex($in_addr);
		$segments = str_split($hex, 4);
		foreach ($segments as &$segment) {
			$segment = hexdec($segment);
		}

		return new static(...$segments);
	}

	/**
	 * @param int|string $integer
	 * @return Ipv6Address
	 * @throws InvalidIpAddressException
	 */
	public static function fromInteger($integer): Ipv6Address {
		if (function_exists('gmp_export')) {
			/** @noinspection PhpParamsInspection */
			$in_addr = gmp_export($integer);

			if ($in_addr !== false) {
				return static::fromBinary(str_pad($in_addr, 16, "\0", STR_PAD_LEFT));
			}
		}

		throw new InvalidIpAddressException();
	}

	/**
	 * @param $ip_address
	 * @return Ipv6Address
	 * @throws InvalidIpAddressException
	 */
	public static function fromString(string $ip_address): Ipv6Address {
		if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
			throw new InvalidIpAddressException("Unrecognized address '{$ip_address}'");
		}

		return static::fromBinary(inet_pton($ip_address));
	}

	/** @inheritDoc */
	public function __toString() {
		$bin = $this->toBinary();
		$compressed = inet_ntop($bin);

		return $compressed;
	}

	/** @inheritDoc */
	public function equals($other) {
		return $other instanceof self && $this->segments === $other->segments;
	}

	public function expand(): string {
		$bin = $this->toBinary();
		$hex = bin2hex($bin);
		$segments = str_split($hex, 4);
		$expanded = implode(':', $segments);

		return $expanded;
	}

	/**
	 * Get the sixteen eight-bit bytes that make up this ip_address.
	 *
	 * @return int[]
	 */
	public function getIterator() {
		foreach ($this->segments as $segment) {
			yield $segment >> 8;
			yield $segment & 0xff;
		}
	}

	/**
	 * Returns the eight sixteen-bit segments that make up this ip_address.
	 *
	 * @return int[]
	 */
	public function getSegments(): array {
		return $this->segments;
	}

	public function isDocumentation(): bool {
		return $this->segments[0] === 0x2001 && $this->segments[1] === 0xdb8;
	}

	/** @inheritDoc */
	public function isGlobal(): bool {
		// TODO: Implement isGlobal() method.
	}

	/** @inheritDoc */
	public function isLoopback(): bool {
		return $this->segments === [0, 0, 0, 0, 0, 0, 0, 1];
	}

	/**
	 * Whether this is a multicast ip_address of the form `ff00::/8`.
	 *
	 * @link http://www.rfc-editor.org/info/rfc3956
	 */
	public function isMulticast(): bool {
		return ($this->segments[0] & 0xff00) === 0xff00;
	}

	public function isUnicastGlobal(): bool {

	}

	public function isUnicastLinkLocal(): bool {
		return ($this->segments[0] & 0xffc0) === 0xfe80;
	}

	public function isUnicastSiteLocal(): bool {
		return ($this->segments[0] & 0xffc0) === 0xfec0;
	}

	public function isUniqueLocal(): bool {
		return ($this->segments[0]  & 0xfe00) === 0xfc00;
	}

	/** @inheritDoc */
	public function isUnspecified(): bool {
		return $this->segments === [0, 0, 0, 0, 0, 0, 0, 0];
	}

	/** @inheritDoc */
	public function toBinary(): string {
		$address = null;

		foreach ($this->segments as $segment) {
			if (isset($address)) {
				$address .= ':';
			}
			$address .= str_pad(dechex($segment), 4, '0', STR_PAD_LEFT);
		}

		return inet_pton($address);
	}

	/** @inheritDoc */
	public function toInteger() {
		if (function_exists('gmp_import')) {
			return (string) gmp_import($this->toBinary());
		}

		return -1;
	}

	/**
	 * @return Ipv4Address|null
	 */
	public function toIpv4Address() {
		if ($this->segments[5] === 0 || $this->segments[5] === 0xffff) {
			$a = $this->segments[6] >> 8;
			//$b = $this->segments[6] & ~0xff00;
			$b = $this->segments[6] & 0xff;
			$c = $this->segments[7] >> 8;
			//$d = $this->segments[7] & ~0xff00;
			$d = $this->segments[7] & 0xff;

			return new Ipv4Address($a, $b, $c, $d);
		}

		return null;
	}

}
