<?php declare(strict_types=1);

namespace AukroApi\DI;

use AukroApi\Client;
use AukroApi\CountryCode;
use AukroApi\Driver\GuzzleSoapClientDriver;
use AukroApi\Identity;
use AukroApi\Session\NetteSessionHandler;
use AukroApi\SoapClient;
use GuzzleHttp\Client as GuzzleClient;
use Nette\DI\CompilerExtension;
use Nette\Utils\Validators;

/**
 * @author Pavel Jurásek
 */
class AukroApiExtension extends CompilerExtension
{

	public function loadConfiguration()
	{
		$config = $this->getConfig();

		Validators::assertField($config, 'username', 'string');
		Validators::assertField($config, 'password', 'string');
		Validators::assertField($config, 'apiKey', 'string');
		Validators::assertField($config, 'versionKey', 'integer');
		Validators::assertField($config, 'url', 'string');
		Validators::assertField($config, 'countryCode');

		if (is_string($config['countryCode'])) {
			$constant = CountryCode::class.'::'.strtoupper($config['countryCode']);

			if (!defined($constant)) {
				throw new BadConfigurationException(sprintf('Unknown country code \'%s\'.', $config['countryCode']));
			}

			$config['countryCode'] = constant($constant);
		}

		$config['countryCode'] = CountryCode::get($config['countryCode']);


		$container = $this->getContainerBuilder();

		// identity
		$container->addDefinition($this->prefix('identity'))
			->setType(Identity::class)
			->setArguments([
				$config['username'],
				$config['password'],
				$config['apiKey'],
			]);

		// driver
		if (isset($config['driver']) && !class_exists($config['driver'])) {
			throw new ClassNotFoundException(sprintf('Driver class %s not found.', $config['driver']));
		}

		if ($container->getByType(GuzzleClient::class) === NULL) {
			$container->addDefinition($this->prefix('httpClient'))
				->setType(GuzzleClient::class);
		}

		$config['driver'] = GuzzleSoapClientDriver::class;

		$container->addDefinition($this->prefix('driver'))
			->setType($config['driver']);

		// soap client
		$container->addDefinition($this->prefix('soapClient'))
			->setType(SoapClient::class)
			->setArguments([$config['url']]);

		// session handler
		if (isset($config['sessionHandler']) && !class_exists($config['sessionHandler'])) {
			throw new ClassNotFoundException(sprintf('Session handler class %s not found.', $config['sessionHandler']));
		}

		$config['sessionHandler'] = NetteSessionHandler::class;

		$container->addDefinition($this->prefix('sessionHandler'))
			->setType($config['sessionHandler']);

		// api client
		$container->addDefinition($this->prefix('client'))
			->setType(Client::class)
			->setArguments([
				'@'.$this->prefix('identity'),
				$config['countryCode'],
				$config['versionKey'],
			]);
	}

}
