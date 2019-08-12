<?php


namespace mvc_router\commands;


use mvc_router\queues\QueueList;

class StartCommand extends Command {
	public function queue(QueueList $queue_list) {
		if($this->param('name')) {
			$queue = $queue_list->get($this->param('name'));
			if($queue) $queue->setCallback('mvc_router\queues\Email::queueCallback')->start();
		}
	}
}