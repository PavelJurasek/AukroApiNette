<?php declare(strict_types=1);

namespace AukroApi\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="payu_transaction_worker")
 * @author Pavel Jurásek
 */
class TransactionWorker
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer
	 */
	private $id;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $lastUpdate;

	/**
	 * @ORM\Column(type="string", nullable=true)
	 * @var string
	 */
	protected $token;

	/**
	 * @ORM\Column(type="bigint", nullable=true)
	 * @var integer
	 */
	protected $userId;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	protected $serverTime;

	public function __construct()
	{
		$this->lastUpdate = new \DateTime('1970-01-01 00:00:00');
	}

	final public function getId(): int
	{
		return $this->id;
	}

	public function getToken(): string
	{
		return $this->token;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getServerTime(): \DateTime
	{
		return $this->serverTime;
	}

	public function getLastUpdate(): \DateTime
	{
		return $this->lastUpdate;
	}

	public function updateSession(\stdClass $response)
	{
		$this->token = $response->sessionHandlePart;
		$this->userId = $response->userId;
		$this->serverTime = new \DateTime('@'.$response->serverTime);
	}

	public function update()
	{
		$this->lastUpdate = new \DateTime;
	}

	public function isLogged(): bool
	{
		return $this->serverTime !== null && $this->serverTime > new \DateTime('-1 hour');
	}

}
