<?php

use mvc_router\confs\Conf;
use mvc_router\dependencies\Dependency;

require_once __DIR__.'/classes/Dependency.php';
require_once __DIR__.'/classes/Conf.php';

Dependency::require_dependency_wrapper();
Conf::require_conf_wrapper();