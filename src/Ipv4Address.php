<?php declare(strict_types=1);
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2016 Richard Fussenegger
 * @license MIT
 */

namespace Fleshgrinder\Network;

use Variable;

/**
 * Immutable value object that represents an arbitrary but valid Internet Protocol ip_address in version 4.
 */
final class Ipv4Address extends IpAddress {

	/**
	 * @var int[]
	 */
	private $bytes;

	/**
	 * Construct new IPv4 ip_address from four eight-bit bytes. The result will represent the IP ip_address `a.b.c.d`.
	 *
	 * @throws InvalidIpAddressException
	 *  if any of the given bytes is not a valid integer or not within 0 and 255.
	 */
	public function __construct(int $a, int $b, int $c, int $d) {
		$bytes = [$a, $b, $c, $d];

		foreach ($bytes as $byte) {
			if ($byte < 0 || $byte > 255) {
				throw new InvalidIpAddressException("Byte must be between 0 and 255, got {$byte}");
			}
		}

		$this->bytes = $bytes;
	}

	public static function __set_state(array $data): Ipv4Address {
		assert(isset($data['bytes']));
		assert(count($data['bytes']) === 4);
		assert(Variable::isStrictArray($data['bytes']));

		return new self(...$data['bytes']);
	}

	/**
	 * Construct new IPv4 ip_address from four eight-bit array elements.
	 *
	 * @param int[] $bytes
	 * @return Ipv4Address
	 * @throws InvalidIpAddressException
	 */
	public static function fromArray(array $bytes): Ipv4Address {
		assert(Variable::isStrictArray($bytes));
		assert(Variable::hasScalarNaturalNumbersOnly($bytes));

		$c = count($bytes);

		if ($c === 4) {
			/** @noinspection PhpParamsInspection */
			return new self(...$bytes);
		}

		throw new InvalidIpAddressException("Invalid array element count, expected 4 got {$c}");
	}

	/**
	 * @param $in_addr
	 * @return Ipv4Address
	 * @throws InvalidIpAddressException
	 */
	public static function fromBinary(string $in_addr): Ipv4Address {
		$address = inet_ntop($in_addr);

		if ($address === false) {
			throw new InvalidIpAddressException('Invalid in_addr value');
		}

		return static::fromString($address);
	}

	/**
	 * @param int|string $integer
	 * @return Ipv4Address
	 * @throws InvalidIpAddressException
	 */
	public static function fromInteger($integer): Ipv4Address {
		$ip_address = long2ip($integer);

		if ($ip_address === false) {
			throw new InvalidIpAddressException('Invalid integer value');
		}

		return static::fromString($ip_address);
	}

	/**
	 * @param $ip_address
	 * @return Ipv4Address
	 * @throws InvalidIpAddressException
	 */
	public static function fromString(string $ip_address): Ipv4Address {
		if (filter_var($ip_address, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
			throw new InvalidIpAddressException("Unrecognized address '{$ip_address}'");
		}

		$bytes = explode('.', $ip_address, 4);

		return new static((int) $bytes[0], (int) $bytes[1], (int) $bytes[2], (int) $bytes[3]);
	}

	/**
	 * Get the human-readable decimal-dot representation of this ip_address.
	 *
	 * @return string
	 */
	public function __toString() {
		return implode('.', $this->bytes);
	}

	/** @inheritDoc */
	public function equals($other) {
		return $other instanceof self && $this->bytes === $other->bytes;
	}

	/**
	 * Get the four eight-bit byte that make up this ip_address.
	 *
	 * @return int[]
	 */
	public function getIterator() {
		foreach ($this->bytes as $byte) {
			yield $byte;
		}
	}

	public function isBroadcast(): bool {
		return $this->bytes === [255, 255, 255, 255];
	}

	/**
	 * Whether this is a documentation ip_address or not, the range of documentation addresses is defines as
	 * `192.0.2.0/24` (TEST-NET-1), `198.51.100.0/24` (TEST-NET-2), and `203.0.113.0/24` (TEST-NET-3).
	 *
	 * @link http://www.rfc-editor.org/info/rfc5737
	 */
	public function isDocumentation(): bool {
		if ($this->bytes[0] === 192 && $this->bytes[1] === 0 && $this->bytes[2] === 2) {
			return true;
		}

		if ($this->bytes[0] === 198 && $this->bytes[1] === 51 && $this->bytes[2] === 100) {
			return true;
		}

		if ($this->bytes[0] === 203 && $this->bytes[1] === 0 && $this->bytes[2] === 113) {
			return true;
		}

		return false;
	}

	/** @inheritDoc */
	public function isGlobal(): bool {
		return !(
			$this->isBroadcast()
			|| $this->isDocumentation()
			|| $this->isLinkLocal()
			|| $this->isLoopback()
			|| $this->isPrivate()
			|| $this->isUnspecified()
		);
	}

	public function isLinkLocal(): bool {
		return $this->bytes[0] === 192 && $this->bytes[1] === 254;
	}

	public function isLoopback(): bool {
		return $this->bytes[0] === 127;
	}

	/**
	 * Whether this is a private ip_address or not, the range of private addresses is defines as `10.0.0.0/8`,
	 * `172.16.0.0/12`, and `192.168.0.0/16`.
	 *
	 * @link http://www.rfc-editor.org/info/rfc1918
	 */
	public function isPrivate(): bool {
		if ($this->bytes[0] === 10) {
			return true;
		}

		if ($this->bytes[0] === 172 && $this->bytes[1] > 15 && $this->bytes[1] < 32) {
			return true;
		}

		if ($this->bytes[0] === 192 && $this->bytes[1] === 168) {
			return true;
		}

		return false;
	}

	/** @inheritDoc */
	public function isUnspecified(): bool {
		return $this->bytes === [0, 0, 0, 0];
	}

	/** @inheritDoc */
	public function toBinary(): string {
		return inet_pton((string) $this);
	}

	/** @inheritDoc */
	public function toInteger() {
		$integer = ip2long((string) $this);

		if (PHP_INT_SIZE === 4) {
			return sprintf('%u', $integer);
		}

		return $integer;
	}

	/**
	 * Converts this ip_address to an IPv4-compatible IPv6 ip_address.
	 *
	 * `a.b.c.d` becomes `::a.b.c.d`
	 */
	public function toIpv6Compatible(): Ipv6Address {
		$g = ($this->bytes[0] << 8) | $this->bytes[1];
		$h = ($this->bytes[2] << 8) | $this->bytes[3];

		return new Ipv6Address(0, 0, 0, 0, 0, 0, $g, $h);
	}

	/**
	 * Converts this ip_address to an IPv4-mapped IPv6 ip_address.
	 *
	 * `a.b.c.d` becomes `::ffff:a.b.c.d`
	 */
	public function toIpv6Mapped(): Ipv6Address {
		$g = ($this->bytes[0] << 8) | $this->bytes[1];
		$h = ($this->bytes[2] << 8) | $this->bytes[3];

		return new Ipv6Address(0, 0, 0, 0, 0, 0xffff, $g, $h);
	}

}
