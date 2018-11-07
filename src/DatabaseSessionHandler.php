<?php declare(strict_types=1);

namespace AukroApi;

use AukroApi\Model\TransactionWorker;
use AukroApi\Session\SessionHandler;
use Doctrine\ORM\EntityManager;

/**
 * @author Pavel Jurásek
 */
class DatabaseSessionHandler implements SessionHandler
{

	/** @var TransactionWorker|null */
	private $worker;

	/** @var EntityManager */
	private $entityManager;

	public function __construct(TransactionWorker $worker, EntityManager $entityManager)
	{
		$this->worker = $worker;
		$this->entityManager = $entityManager;
	}

	/** @return \stdClass|null */
	public function load()
	{
		if (!$this->worker || !$this->worker->isLogged()) {
			return null;
		}

		$result = new \stdClass;
		$result->sessionHandlePart = $this->worker->getToken();
		$result->userId = $this->worker->getUserId();
		$result->serverTime = $this->worker->getServerTime()->format('U');

		return $result;
	}

	public function store(\stdClass $loginSession)
	{
		if ($this->worker) {
			$this->worker->updateSession($loginSession);
			$this->entityManager->flush($this->worker);
		}
	}

	public function clear()
	{
		$this->worker = null;
	}

}
