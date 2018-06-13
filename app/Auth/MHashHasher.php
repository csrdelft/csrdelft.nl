<?php

namespace App\Auth;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 12/03/2018
 */
class MHashHasher implements HasherContract {

	/**
	 * Get information about the given hashed value.
	 *
	 * @param  string $hashedValue
	 * @return array
	 */
	public function info($hashedValue) {
		return password_get_info($hashedValue);
	}

	/**
	 * Hash the given value.
	 *
	 * @param  string $value
	 * @param  array $options
	 * @return string
	 */
	public function make($value, array $options = []) {
		$salt = \mhash_keygen_s2k(MHASH_SHA1, $value, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
		return "{SSHA}" . base64_encode(mhash(MHASH_SHA1, $value . $salt) . $salt);
	}

	/**
	 * Check the given plain value against a hash.
	 *
	 * @param  string $value
	 * @param  string $hashedValue
	 * @param  array $options
	 * @return bool
	 */
	public function check($value, $hashedValue, array $options = []) {
		$ohash = base64_decode(substr($hashedValue, 6));
		$osalt = substr($ohash, 20);
		$ohash = substr($ohash, 0, 20);
		$nhash = pack("H*", sha1($value . $osalt));
		if ($ohash === $nhash) {
			return true;
		}
		return false;
	}

	/**
	 * Check if the given hash has been hashed using the given options.
	 *
	 * @param  string $hashedValue
	 * @param  array $options
	 * @return bool
	 */
	public function needsRehash($hashedValue, array $options = []) {
		return false;
	}
}
