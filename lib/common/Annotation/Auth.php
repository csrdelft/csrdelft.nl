<?php


namespace CsrDelft\common\Annotation;


use CsrDelft\events\AccessControlEventListener;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 *
 * Een route wordt geforceerd om óf een _mag default value hebben óf een @Auth annotatie met een mag waarde.
 *
 * @see AccessControlEventListener hier wordt deze annotatie geforceerd
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 */
class Auth {
	/**
	 * @var string
	 */
	private $mag;

	public function __construct(array $data) {
		if (isset($data['value'])) {
			if (is_array($data['value'])) {
				$data['mag'] = implode(',', $data['value']);
			} else {
				$data['mag'] = $data['value'];
			}
			unset($data['value']);
		}

		foreach ($data as $key => $value) {
			$method = 'set'.str_replace('_', '', $key);
			if (!method_exists($this, $method)) {
				throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, static::class));
			}
			$this->$method($value);
		}
	}

	public function setMag($mag) {
		$this->mag = $mag;
	}

	public function getMag() {
		return $this->mag;
	}

}
