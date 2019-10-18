<?php


namespace mvc_router\commands;


use mvc_router\helpers\Helpers;
use mvc_router\parser\PHPDocParser;
use mvc_router\services\FileSystem;
use ReflectionException;

class HelpCommand extends Command {
	/**
	 * @syntax --help
	 * @syntax help:index -p cmd=<value> [method=<value>?]
	 *
	 * @param PHPDocParser $parser
	 * @param FileSystem $fs
	 * @param Helpers $helper
	 * @return array|string
	 * @throws ReflectionException
	 */
	public function index(PHPDocParser $parser, FileSystem $fs, Helpers $helper) {
		if(!$this->param('cmd') && !$this->param('method')) {
			$cmds = [];
			$fs->browse_dir(function($path) use(&$cmds, $helper, $parser) {
				$path = explode($helper->get_slash(), $path);
				$cmd = end($path);
				$cmd = explode('.', $cmd)[0];
				$cmd = str_replace('Command', '', $cmd);
				$cmd = strtolower($cmd);
				$methods = $parser->get_class_methods(
					$this->inject->{'get_command_'.$cmd}(),
					PHPDocParser::COMMAND
				);
				$cmds[$cmd] = $methods;
			},  false, __DIR__);
			$tmp = [];
			foreach($cmds as $key => $values) {
				$tmp[] = '|=========================| '.$key.' |=========================|';
				if(count($values) === 0) {
					$tmp[] = '|= Aucune methode dans cette commande';
				}
				foreach($values as $value) {
					$doc = $parser->get_method_doc($this->inject->{'get_command_'.$key}(), $value);
					$syntaxes = (isset($doc['syntax']) ? $doc['syntax'] : $key.':'.$value);
					if(is_array($syntaxes)) {
						$syntaxes = implode(', ', $syntaxes);
					}
					$tmp[] = "|= {$value} -> php exe.php {$syntaxes}";
				}
			}
			return $tmp;
		}
		elseif(!$this->param('method')) {
			return $parser->get_method_doc(
				$this->inject->{'get_command_'.$this->param('cmd')}(),
				'index'
			);
		}
		elseif(!$this->param('cmd')) {
			return 'ERREUR : LE PARAMETRE \'cmd\' EST REQUIS SI LE PARAMETRE \'method\' EST FOURNIS !';
		}
		return 'help';
	}
	
	/**
	 * @param PHPDocParser $PHPDocParser
	 * @return array|mixed
	 * @throws ReflectionException
	 */
	public function home(PHPDocParser $PHPDocParser) {
		return $this->var_dump($PHPDocParser->get_method_doc($this->get_class(), 'home'));
	}
}