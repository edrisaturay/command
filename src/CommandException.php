<?php namespace Edrisa\Command;

/**
 * Class CommandException is thrown when a command fails
 * @package Edrisa\Command
 */
class CommandException extends Exception {

	protected $cmd;

	public function __construct(Command $cmd, $message)
	{
		$this->cmd = $cmd;
		parent::__construct($message, $cmd->getExitCode());
	}

	public function getCommand()
	{
		return $this->cmd;
	}
}
