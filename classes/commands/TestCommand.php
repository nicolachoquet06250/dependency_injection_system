<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\confs\Mysql;
use mvc_router\data\gesture\Manager;
use mvc_router\data\gesture\pizzygo\managers\Address;
use mvc_router\data\gesture\pizzygo\managers\AddressType;
use mvc_router\data\gesture\pizzygo\managers\Comment;
use mvc_router\data\gesture\pizzygo\managers\Credential;
use mvc_router\data\gesture\pizzygo\managers\Email;
use mvc_router\data\gesture\pizzygo\managers\EndStatus;
use mvc_router\data\gesture\pizzygo\managers\Like;
use mvc_router\data\gesture\pizzygo\managers\Order;
use mvc_router\data\gesture\pizzygo\managers\OrderStatus;
use mvc_router\data\gesture\pizzygo\managers\Phone;
use mvc_router\data\gesture\pizzygo\managers\Product;
use mvc_router\data\gesture\pizzygo\managers\ProductCategory;
use mvc_router\data\gesture\pizzygo\managers\Role;
use mvc_router\data\gesture\pizzygo\managers\Shop;
use mvc_router\data\gesture\pizzygo\managers\User;
use mvc_router\data\gesture\pizzygo\managers\Variant;
use mvc_router\services\FileSystem;
use mvc_router\services\Password;
use mvc_router\services\Trigger;
use ReflectionException;

class TestCommand extends Command {
	/**
	 * @param Commands $commands
	 * @throws Exception
	 */
	public function server(Commands $commands) {
		$port = '2107';
		$directory = __DIR__.'/../../';
		if($this->param('port')) {
			$port = $this->param('port');
		}

		if($this->param('directory')) {
			$directory .= $this->param('directory');
		}
		$directory = realpath($directory);
		$commands->run('php -S localhost:'.$port.' -t '.$directory);
	}

	/**
	 * @param Trigger $triggers
	 */
	public function helper_is_cli(Trigger $triggers) {
		$triggers->trig('trigger_test', 'test');
	}

	/**
	 * @return array|string
	 */
	public function mysql() {
		$mysql = $this->confs->get_mysql();
		if(!$mysql->is_connected()) {
			return 'ERROR : Mysql not connected';
		}
		$mysql->query('SELECT * FROM `user` WHERE email = $1 OR email = $2', [
			'nicolachoquet06250@gmail.com',
			'yannchoquet@gmail.com'
		]);
		$users = [];
		while ($data = $mysql->fetch_assoc()) {
			$users[] = $data;
		}
		return $users;
	}

	/**
	 * @param User        $userManager
	 * @param Password    $passwordService
	 * @return bool|\mvc_router\data\gesture\pizzygo\entities\User|\mvc_router\data\gesture\pizzygo\entities\User[]
	 * @throws Exception
	 */
	public function managers(User $userManager, Password $passwordService) {
		$password = $passwordService->b_crypt('2669NICOLAS2107');
		$user = $userManager->get_all_from_email('nicolachoquet06251@gmail.com');
		if(!$user) {
			$user = $userManager
				->set_address_email_phone_password_description_website_pseudo_firstname_lastname(
					"1102 ch de l'espagnol", 'nicolachoquet06251@gmail.com', '0763207630',
					$password, 'une description',
					'nicolaschoquet.fr', 'nicolachoquet06250',
					'Nicolas', 'Choquet');
		}
		if(!$user->get('active')) $user->set('active', true);
		if(!$user->get('fb_id')) $user->set('fb_id', 20225555);
		$user->save();

		$user = $userManager->get_all_from_pseudo('nicolachoquet06250');
		return $user && $passwordService->is_valid('2669NICOLAS2107', $user->get('password')) ? $user : false;
	}

	/**
	 * @param Address         $addressManager
	 * @param AddressType     $addressTypeManager
	 * @param Comment         $commentManager
	 * @param Credential      $credentialManager
	 * @param Email           $emailManager
	 * @param EndStatus       $endStatusManager
	 * @param Like            $likeManager
	 * @param Order           $orderManager
	 * @param OrderStatus     $orderStatusManager
	 * @param Phone           $phoneManager
	 * @param Product         $productManager
	 * @param ProductCategory $productCategoryManager
	 * @param Role            $roleManager
	 * @param Shop            $shopManager
	 * @param User            $userManager
	 * @param Variant         $variantManager
	 * @return bool[]
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function managers_create_table(Address $addressManager, AddressType $addressTypeManager,
										  Comment $commentManager, Credential $credentialManager,
										  Email $emailManager, EndStatus $endStatusManager,
										  Like $likeManager, Order $orderManager,
										  OrderStatus $orderStatusManager, Phone $phoneManager,
										  Product $productManager, ProductCategory $productCategoryManager,
										  Role $roleManager, Shop $shopManager, User $userManager,
										  Variant $variantManager) {
		$args = func_get_args();
		$result = [];
		/** @var Manager $manager */
		foreach ($args as $manager) {
			$result[$manager->get_table()] = $manager->create_table() ? 'success' : 'failed';
		}
		return $result;
	}

	/**
	 * @param FileSystem $fileSystem
	 * @return string|null
	 */
	public function number_of_lines_in_project(FileSystem $fileSystem) {
		$nb_lines = 0;
		$void = !is_null($this->param('not-void')) ? !$this->param('not-void') : null;
		$fileSystem->browse_root(function ($elem) use ($fileSystem, &$nb_lines, $void) {
			$content = explode("\n", $fileSystem->read_file($elem));
			if(is_null($void)) $nb_lines = $nb_lines + count($content);
			elseif (is_bool($void)) {
				if($void) foreach ($content as $item) {
					if (trim($item) === "") {
						$nb_lines++;
					}
				}
				else foreach ($content as $item) {
					if (trim($item) !== "") {
						$nb_lines++;
					}
				}
			}
		}, true);
		if(is_null($void)) return "L'application comporte {$nb_lines} lignes de code !";
		elseif (is_bool($void)) return $void ? "L'application comporte {$nb_lines} lignes de code vides !"
			: "L'application comporte {$nb_lines} lignes de code non vides !";
		else return null;
	}
}