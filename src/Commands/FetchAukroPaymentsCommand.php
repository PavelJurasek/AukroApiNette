<?php declare(strict_types=1);

namespace AukroApi\Commands;

use AukroApi\Client;
use AukroApi\DatabaseSessionHandler;
use AukroApi\Driver\DriverRequestFailedException;
use AukroApi\LoginFailedException;
use AukroApi\Model\Transaction;
use AukroApi\Model\TransactionDetail;
use AukroApi\Model\TransactionWorker;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use GuzzleHttp\Exception\ServerException;
use Nette\DI\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\Debugger;

/**
 * @author Pavel Jurásek
 */
class FetchAukroPaymentsCommand extends Command
{

	protected function configure()
	{
		$this->setName('aukro:payments')
			->addOption('since', null, InputOption::VALUE_OPTIONAL, 'Date in format Y-m-d')
			 ->setDescription('Fetch Aukro payments');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		/** @var Container $container */
		$container = $this->getHelper('container');

		/** @var Client $client */
		$client = $this->getHelper('container')->getByType(Client::class);

		/** @var EntityManager $entityManager */
		$entityManager = $this->getHelper('container')->getByType(EntityManager::class);

		/** @var EntityRepository $transactions */
		$transactions = $entityManager->getRepository(Transaction::class);

		/** @var TransactionWorker|null $worker */
		$worker = $entityManager->find(TransactionWorker::class, 1);

		if ($worker === null) {
			$worker = new TransactionWorker;

			$entityManager->persist($worker);
			$entityManager->flush();
		}

		$client->setSessionHandler(new DatabaseSessionHandler($worker, $entityManager));

		if (!$worker->isLogged()) {
			try {
				$client->login();

			} catch (LoginFailedException $e) {
				Debugger::log($e);
				$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
				return;
			}
		}

		try {
			/** @var \stdClass $result */
			$result = $client->getMyIncomingPayments([
				'transRecvDateFrom' => $worker->getLastUpdate()->format('U'),
			]);
		} catch (\SoapFault $e) {
			Debugger::log($e);
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
			return;
		} catch (ServerException $e) {
			Debugger::log($e);
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
			return;
		} catch (DriverRequestFailedException $e) {
			$output->writeln(sprintf('<error>%s</error>', $e->getMessage()));
			return;
		}

		$existingResult = $entityManager
			->createQuery(sprintf('SELECT t.id FROM %s t', Transaction::class))
			->getResult();

		$existingTransactions = array_column($existingResult, 'id');

		if (isset($result->payTransIncome->item)) {
			/** @var \stdClass $item */
			foreach ($result->payTransIncome->item as $trans) {
				if (in_array($trans->payTransId, $existingTransactions)) { // duplicate transaction
					$output->writeln(sprintf('<comment>Skipping transaction ID %s</comment>', $trans->payTransId));
					continue;
				}

				$transaction = new Transaction(
					$trans->payTransId,
					(int) $trans->payTransItId,
					$trans->payTransBuyerId,
					$trans->payTransType,
					$trans->payTransStatus,
					$trans->payTransAmount,
					new \DateTime('@' . $trans->payTransCreateDate),
					new \DateTime('@' . $trans->payTransRecvDate),
					$trans->payTransPrice,
					$trans->payTransCount,
					$trans->payTransPostageAmount,
					$trans->payTransIncomplete === 0
				);

				foreach ($trans->payTransDetails as $transDetail) {
					$detail = new TransactionDetail(
						$transaction,
						$transDetail->payTransDetailsItId,
						$transDetail->payTransDetailsPrice,
						$transDetail->payTransDetailsCount
					);

					$entityManager->persist($detail);
				}

				$entityManager->persist($transaction);

				$output->writeln(sprintf('<info>%s</info>', $transaction->getId()));
			}
		}

		$worker->update();

		$entityManager->flush();
	}

}
