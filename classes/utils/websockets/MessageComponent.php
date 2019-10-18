<?php
	
	
	namespace mvc_router\websockets;
	
	
	use mvc_router\Base;
	use Ratchet\ConnectionInterface;
	use Ratchet\MessageComponentInterface;
	
	abstract class MessageComponent extends Base implements MessageComponentInterface {
		protected $clients;
		
		public function __construct() {
			parent::__construct();
			$this->clients = new \SplObjectStorage;
		}
		
		public function sendBroadcast( ConnectionInterface $from, $msg ) {
			foreach( $this->clients as $client ) {
				if( $from !== $client ) $client->send( $msg );
			}
		}
		
		public abstract function onOpen( ConnectionInterface $conn );
		
		public abstract function onMessage( ConnectionInterface $from, $msg );
		
		public abstract function onClose( ConnectionInterface $conn );
		
		public abstract function onError( ConnectionInterface $conn, \Exception $e );
	}