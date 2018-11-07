<?php declare(strict_types=1);

namespace AukroApi\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="payu_transaction")
 * @author Pavel Jurásek
 */
class Transaction
{

	/**
	 * @ORM\Id()
	 * @ORM\Column(type="bigint")
	 * @var int
	 */
	protected $id;

	/**
	 * @ORM\Column(type="bigint", options={"unsigned"=true})
	 * @var int
	 */
	protected $offerId;

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	protected $buyerId;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $type;

	/**
	 * @ORM\Column(type="string")
	 * @var string
	 */
	protected $status;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $amount;

	/**
	 * @ORM\Column(type="datetime")
	 * @var \DateTime
	 */
	protected $createdAt;

	/**
	 * @ORM\Column(type="datetime", nullable=true)
	 * @var \DateTime
	 */
	protected $completedAt;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $price;

	/**
	 * @ORM\Column(type="integer")
	 * @var integer
	 */
	protected $count;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $shippingPrice;

	/**
	 * @ORM\OneToMany(targetEntity="TransactionDetail", mappedBy="transaction", cascade={"persist"})
	 * @var Collection
	 */
	protected $details;

	/**
	 * @ORM\Column(type="boolean")
	 * @var boolean
	 */
	protected $completed;

	public function __construct(int $id, int $offerId, int $buyerId, string $type, string $status, float $amount, \DateTime $createdAt, \DateTime $completedAt, float $price, int $count, float $shippingPrice, bool $completed)
	{
		$this->id = $id;
		$this->offerId = $offerId;
		$this->buyerId = $buyerId;
		$this->type = $type;
		$this->status = $status;
		$this->amount = $amount;
		$this->createdAt = $createdAt;
		$this->completedAt = $completedAt;
		$this->price = $price;
		$this->count = $count;
		$this->shippingPrice = $shippingPrice;
		$this->completed = $completed;

		$this->details = new ArrayCollection;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function getOfferId(): int
	{
		return $this->offerId;
	}

	public function getBuyerId(): int
	{
		return $this->buyerId;
	}

	public function getType(): string
	{
		return $this->type;
	}

	public function getStatus(): string
	{
		return $this->status;
	}

	public function getAmount(): float
	{
		return $this->amount;
	}

	public function getCreatedAt(): \DateTime
	{
		return $this->createdAt;
	}

	public function getCompletedAt(): \DateTime
	{
		return $this->completedAt;
	}

	public function getPrice(): float
	{
		return $this->price;
	}

	public function getCount(): int
	{
		return $this->count;
	}

	public function getShippingPrice(): float
	{
		return $this->shippingPrice;
	}

	/**
	 * @return TransactionDetail[]
	 */
	public function getDetails(): array
	{
		return $this->details->toArray();
	}

	/**
	 * @internal
	 */
	public function addDetail(TransactionDetail $detail)
	{
		$this->details->add($detail);
	}

	public function isCompleted(): bool
	{
		return $this->completed;
	}

}
