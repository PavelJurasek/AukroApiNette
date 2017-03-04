<?php

/**
 * Test: AukroApi\DI\AukroApiExtension.
 *
 * @testCase Test\AukroApi\DI\AukroApiExtensionTest
 * @author Pavel Jurásek
 * @package App\AukroApi\DI
 */

namespace Test\AukroApi\DI;

use AukroApi\DI\AukroApiExtension;
use AukroApi\DI\BadConfigurationException;
use Nette\Bridges\HttpDI\HttpExtension;
use Nette\Bridges\HttpDI\SessionExtension;
use Nette\DI\Compiler;
use Nette\Utils\AssertionException;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

/**
 * @author Pavel Jurásek
 */
class AukroApiExtensionTest extends Tester\TestCase
{

    public function testCompile()
    {
    	$config = [
			'username' => 'username',
			'password' => 'password',
			'apiKey' => 'abc',
			'versionKey' => 123,
			'url' => 'example.com',
		];


    	$compiler = $this->getCompiler();
		$compiler->addConfig(['aukro' => $config]);
		Assert::exception(function () use ($compiler) {
			$compiler->compile();
		}, AssertionException::class, 'Missing item \'countryCode\' in array.');


		$config['countryCode'] = 'uk';
		$compiler = $this->getCompiler();
		$compiler->addConfig(['aukro' => $config]);
		Assert::exception(function () use ($compiler) {
			$compiler->compile();
		}, BadConfigurationException::class);


		$config['countryCode'] = 56;
		$compiler = $this->getCompiler();
		$compiler->addConfig(['aukro' => $config]);
		Assert::noError(function () use ($compiler) {
			$compiler->compile();
		});


		$config['countryCode'] = 'pl';
		$compiler = $this->getCompiler();
		$compiler->addConfig(['aukro' => $config]);
		Assert::noError(function () use ($compiler) {
			$compiler->compile();
		});
    }

	private function getCompiler(): Compiler
	{
		$compiler = new Compiler();

		$compiler->addExtension('session', new SessionExtension);
		$compiler->addExtension('http', new HttpExtension);
		$compiler->addExtension('aukro', new AukroApiExtension);

		return $compiler;
    }

    public function tearDown()
    {
    	parent::tearDown();

    	\Mockery::close();
    }

}

(new AukroApiExtensionTest())->run();
