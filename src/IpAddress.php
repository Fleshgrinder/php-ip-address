<?php declare(strict_types=1);
/**
 * @author Richard Fussenegger <fleshgrinder@users.noreply.github.com>
 * @copyright 2016 Richard Fussenegger
 * @license MIT
 */

namespace Fleshgrinder\Network;

use Fleshgrinder\Core\Equalable;
use Fleshgrinder\Core\Stringable;
use IteratorAggregate;
use JsonSerializable;
use Variable;

/**
 * Immutable value object that represents an arbitrary but valid Internet Protocol address in version 4 or 6.
 */
abstract class IpAddress implements Equalable, IteratorAggregate, JsonSerializable, Stringable {

	/**
	 * Construct new instance from an array of bytes or segments (two bytes).
	 *
	 * Input data can be given as an array with …
	 *
	 * 1. … four eight-bit (byte) elements which will be interpreted as an IPv4 address.
	 * 2. … eight sixteen-bit (two byte) elements which will be interpreted as an IPv6 address.
	 * 3. … sixteen eight-bit (byte) elements which will be interpreted as an IPv6 address.
	 *
	 * ## Examples
	 * ### IPv4
	 * ```php
	 * $ipv4 = IpAddress::fromArray([0b1111111, 0b0, 0b0, 0b1]);
	 * echo $ipv4; // 127.0.0.1
	 * ```
	 *
	 * ### IPv6 Segments
	 * ```php
	 * $ipv6 = IpAddress::fromArray([0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x0, 0x1]);
	 * echo $ipv6; // ::1
	 * ```
	 *
	 * ### IPv6
	 * ```php
	 * $ipv6 = IpAddress::fromArray([0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b0, 0b1]);
	 * echo $ipv6; // ::1
	 * ```
	 *
	 * @param int[] $bytes
	 *  of four elements for an IPv4 address, of eight two byte segments or sixteen elements for an IPv6 address.
	 * @return IpAddress|Ipv4Address|Ipv6Address
	 *  depending on the given input data.
	 * @throws InvalidIpAddressException
	 *  if input data does not represent a valid IPv4 or IPv6 address.
	 */
	public static function fromArray(array $bytes): IpAddress {
		assert(Variable::isStrictArray($bytes));
		assert(Variable::hasScalarNaturalNumbersOnly($bytes));

		$c = count($bytes);

		if ($c === 4) {
			return Ipv4Address::fromArray($bytes);
		}

		if ($c === 8 || $c === 16) {
			return Ipv6Address::fromArray($bytes);
		}

		throw new InvalidIpAddressException("Invalid array element count, expected 4, 8 or 16 got {$c}.");
	}

	/**
	 * Construct new instance from a binary packed numeric `in_addr` structure.
	 *
	 * >! This method triggers a warning if an invalid `in_addr` structure is encountered due to the usage of PHPʼs
	 * >! `inet_ntop` function. This warning is not suppressed because construction from binary data usually happens
	 * >! from internal data and not from data provided by a user. Hence, these situations should be logged and acted
	 * >! upon and not simply ignored.
	 *
	 * ## Examples
	 * ```php
	 * $ipv4 = IpAddress::fromBinary(inet_pton('127.0.0.1'));
	 * $ipv6 = IpAddress::fromBinary(inet_pton('::1'));
	 * ```
	 *
	 * @see inet_ntop()
	 * @see inet_pton()
	 * @see Fleshgrinder\Network\Ipv4Address::fromBinary()
	 * @see Fleshgrinder\Network\Ipv6Address::fromBinary()
	 * @param string $in_addr
	 *  binary packed numeric structure to construct the instance from.
	 * @return IpAddress|Ipv4Address|Ipv6Address
	 *  depending on the given input data.
	 * @throws InvalidIpAddressException
	 *  if input data does not represent a valid IPv4 or IPv6 address.
	 */
	public static function fromBinary(string $in_addr): IpAddress {
		$bytes = strlen($in_addr);

		if ($bytes === 4) {
			return Ipv4Address::fromBinary($in_addr);
		}

		if ($bytes === 16) {
			return Ipv6Address::fromBinary($in_addr);
		}

		throw new InvalidIpAddressException("Invalid byte count, expected 4 or 16 got {$bytes}.");
	}

	/**
	 * Construct new instance from human-readable decimal-dot for IPv4 or hex-colon for IPv6 string. Note that this
	 * method can handle both compressed and expanded IPv6 hex-colon notations.
	 *
	 * ## Examples
	 * ```php
	 * $ipv4 = IpAddress::fromString('127.0.0.1');
	 * $ipv6 = IpAddress::fromString('::1');
	 * ```
	 *
	 * @param $ip_address
	 *  in human-readable decimal-dot for IPv4 or hex-colon for IPv6 notation.
	 * @return IpAddress|Ipv4Address|Ipv6Address
	 *  depending on the given input data.
	 * @throws InvalidIpAddressException
	 *  if input data does not represent a valid IPv4 or IPv6 address.
	 */
	public static function fromString(string $ip_address): IpAddress {
		if (strpos($ip_address, ':') !== false) {
			return Ipv6Address::fromString($ip_address);
		}

		if (strpos($ip_address, '.') !== false) {
			return Ipv4Address::fromString($ip_address);
		}

		throw new InvalidIpAddressException("Unrecognized address '{$ip_address}'.");
	}

	/**
	 * Whether this is considered a globally routable address or not.
	 *
	 * >! This does not mean that this address *is* globally routed.
	 */
	abstract public function isGlobal(): bool;

	/**
	 * Whether this is the special unspecified address that consists solely of zeros.
	 *
	 * @link http://www.rfc-editor.org/info/rfc4291
	 * @link https://www.rfc-editor.org/info/rfc6890
	 */
	abstract public function isUnspecified(): bool;

	/**
	 * Get the human-readable notation of this address escaped for usage in a JSON document.
	 */
	final public function jsonSerialize() {
		return (string) $this;
	}

	/**
	 * Get binary packed numeric `in_addr` representation of this address which is ideal for persistent storage of this
	 * address. This is because it only requires four bytes of storage for an IPv4 address and sixteen bytes for an IPv6
	 * address. Interoperability is guaranteed in most software because the required functions to interpret the data is
	 * built-in in almost all operating systems.
	 *
	 * For instance `INET6_NTOA` may be used in [MariaDB](https://mariadb.com/kb/en/mariadb/inet6_ntoa/) and
	 * [MySQL](](http://dev.mysql.com/doc/refman/5.7/en/miscellaneous-functions.html#function_inet6-ntoa)) to convert it
	 * back to a human-readable non-binary string. It is best to use a `BINARY(16)` field in a database to store the
	 * result of this method and to remove the null byte padding upon selection, e.g.:
	 *
	 * ```sql
	 * CREATE TABLE `t` (`c` BINARY(16));
	 * INSERT INTO `t` SET `c` = INET6_ATON('127.0.0.1');
	 * SELECT TRIM(TRAILING '\0' FROM `c`) AS `c` FROM `t`;
	 * ```
	 *
	 * The selected data can then directly be used to recreate an IP instance as follows:
	 *
	 * ```php
	 * $c = $db->query('SELECT TRIM(TRAILING '\0' FROM `c`) AS `c` FROM `t`')->fetchField('c');
	 * $ip = IpAddress::fromBinary($c);
	 * ```
	 *
	 * @see inet_pton()
	 * @see Fleshgrinder\Network\IpAddress::fromBinary()
	 */
	abstract public function toBinary(): string;

	/**
	 * Convert this address to an integer. The returned value is either a scalar int or a string, depending on the
	 * capabilities of the PHP platform.
	 *
	 * >! Conversion of IPv6 addresses fails if the GMP extension is not available and `-1` will be returned.
	 *
	 * @return int|string
	 */
	abstract public function toInteger();

}
