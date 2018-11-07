<?php declare(strict_types=1);

namespace AukroApi\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="payu_transaction_detail")
 * @author Pavel Jurásek
 */
class TransactionDetail
{

	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue
	 * @var integer
	 */
	private $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Transaction", inversedBy="details", cascade={"persist"})
	 * @var Transaction
	 */
	protected $transaction;

	/**
	 * @ORM\Column(type="bigint")
	 * @var int
	 */
	protected $offerId;

	/**
	 * @ORM\Column(type="float")
	 * @var float
	 */
	protected $price;

	/**
	 * @ORM\Column(type="integer")
	 * @var int
	 */
	protected $count;

	public function __construct(Transaction $transaction, int $offerId, float $price, int $count)
	{
		$this->transaction = $transaction;
		$this->offerId = $offerId;
		$this->price = $price;
		$this->count = $count;

		$transaction->addDetail($this);
	}

	final public function getId(): int
	{
		return $this->id;
	}

	public function getOfferId(): int
	{
		return $this->offerId;
	}

	public function getPrice(): float
	{
		return $this->price;
	}

	public function getCount(): int
	{
		return $this->count;
	}

}
