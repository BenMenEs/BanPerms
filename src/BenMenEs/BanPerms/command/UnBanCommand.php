<?php

declare(strict_types=1);

namespace BenMenEs\BanPerms\command;

use BenMenEs\BanPerms\API;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class UnBanCommand extends Command
{
	
	/** @var API */
	private $api;
	
	public function __construct(API $api, string $name, string $description, string $permission)
	{
		$this->api = $api;
		
		parent::__construct($name, $description);
		$this->setPermission($permission);
	}
	
	public function execute(CommandSender $sender, string $label, array $args) : void
	{
		if(!$this->testPermission($sender)) return;
		
		$api = $this->api;
		
		if(empty($args[0]))
		{
			$sender->sendMessage("§cВведите ник игрока");
			return;
		}
		
		$target = array_shift($args);
		
		if($api->getData($target) === null)
		{
			$sender->sendMessage("§cИгрок не забанен");
			return;
		}
		
		$api->unban($target);
		$api->getServer()->broadcastMessage(str_replace(["{unbanned}", "{unbanby}"], [$target, $sender->getName()], "Игрок §c{unbanned} §rбыл разбанен §c{unbanby}"));
	}
}