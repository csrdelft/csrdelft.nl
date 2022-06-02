<?php


namespace CsrDelft\common\Logging;


use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Logger;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author G.J.W. Oolbekkink <g.j.w.oolbekkink@gmail.com>
 * @since 2020-08-17
 * @see /config/packages/prod/monolog.yaml
 */
class LogActivationStrategy implements ActivationStrategyInterface
{

	public function isHandlerActivated(array $record): bool
	{
		if ($record['level'] <= Logger::WARNING) {
			return false;
		}

		if (!isset($record['context']['exception'])) {
			return true;
		}

		$exception = $record['context']['exception'];

		// Alleen http status 500 loggen
		if ($exception instanceof HttpException) {
			return $exception->getStatusCode() == 500;
		}

		if ($exception instanceof AccessDeniedException) {
			return false;
		}

		return true;
	}
}
