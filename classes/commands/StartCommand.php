<?php


namespace mvc_router\commands;


use Exception;
use mvc_router\services\Logger;

class StartCommand extends Command {
	/**
	 * @syntax [project] start:websocket_server -p [host=<value>?localhost] [address=<value>?127.0.0.1] [port=<value>?8080]
	 *
	 * @param Logger $logger
	 */
	public function websocket_server(Logger $logger) {
		$websocket = $this->confs->get_websocket_routes();
		$logger->types( Logger::CONSOLE);
		[$host, $address, $port] = [$this->param('host'), $this->param('address'), $this->param('port')];
		if(!$port) $port = 8080;
		if(!$host) $host = 'localhost';
		if(!$address) $address = '127.0.0.1';
		
		$app = $this->inject->get_ratchet_app_ws($host, $port, $address);
		foreach( $websocket->get_routes() as $route => $details ) {
			$app->route($route, $details['controller'], $details['allows']);
		}
		$logger->log( "Serveur de websokets lancé sur l'url ws://{$host}:{$port} ou ws://{$address}:{$port}");
		$app->run();
	}
	
	/**
	 * @syntax [directory] start:server -p [port=<value>?8080]
	 *
	 * @param Commands $commands
	 * @throws Exception
	 */
	public function server(Commands $commands) {
		$port = $this->param('port');
		$directory = __DIR__.'/../../'.__SITE_NAME__;
		if(!$port) $port = 8080;
		
		$directory = realpath($directory);
		$commands->run('php -S localhost:'.$port.' -t '.$directory);
	}
}