<?php

declare(strict_types=1);

namespace BenMenEs\BanPerms\command;

use BenMenEs\BanPerms\API;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class BanCommand extends Command
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
		
		$target = $api->getServer()->getPlayer(array_shift($args));
		
		if($target === null)
		{
			$sender->sendMessage("§cИгрок не найден");
			return;
		}
		
		if($sender instanceof Player)
		{
			if($api->getPriority($sender) < $api->getPriority($target))
			{
				$sender->sendMessage("§cВы не можете забанить данного игрока");
				return;
			}
		}
		
		if(empty($args[0]))
		{
			$sender->sendMessage("§cВведите разбан в минутах");
			return;
		}
		
		$unban = array_shift($args);
		
		if(!is_numeric($unban))
		{
			$sender->sendMessage("§cВведите разбан в цифрах");
			return;
		}
		
		$unban = (int) $unban;
		
		if(empty($args[0]))
		{
			$sender->sendMessage("§cВведите причину бана");
			return;
		}
		
		$reason = implode(" ", $args);
		
		$api->ban($target->getName(), $sender->getName(), $reason, $unban);
		$api->getServer()->broadcastMessage(str_replace(["{banby}", "{banned}", "{reason}", "{unban}"], [$sender->getName(), $target->getName(), $reason, $unban], "Игрок §c{banned} §rзабанен игроком §c{banby} §rразбан через: §a{unban} §rминут причина: §e{reason}"));
	}
}