<?php

declare(strict_types=1);

namespace BenMenEs\BanPerms;

use BenMenEs\BanPerms\command\BanCommand;
use BenMenEs\BanPerms\command\UnBanCommand;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\Player;

class API extends PluginBase
{
	
	/** @var String => Integer */
	public $groups;
	
	/** @var Config */
	private $banlist;
	
	/** @var API|null */
	private static $instance = null;
	
	public function onLoad() : void
	{
		self::$instance = $this;
		
		$map = $this->getServer()->getCommandMap();
		
		$commands = [
		  $map->getCommand("ban"),
		  $map->getCommand("pardon")
		];
		
		foreach($commands as $command)
		    $map->unregister($command);
		
		$commands = [
		  new BanCommand($this, "ban", "Забанить игрока", "ban.cmd"),
		  new UnBanCommand($this, "unban", "Разбанить игрока", "unban.cmd")
		];
		
		foreach($commands as $command)
		    $map->register("BanPerms", $command);
	}
	
	public function onEnable() : void
	{
		$this->saveDefaultConfig();
		$this->groups = $this->getConfig()->get("groups");
		
		$this->banlist = new Config($this->getDataFolder() . "banlist.json", Config::JSON);
		
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}
	
	private static function getInstance() 
	{
		return self::$instance;
	}
	
	/**
	 * Вернёт уровень игрока по привелегии из конфига
	 * Если там нету его привилегии, то вернёт default
	 */
	public function getPriority(Player $player) : int
	{
		$group = $this->getServer()->getPluginManager()->getPlugin("PurePerms")->getUserDataMgr()->getGroup($player)->getName();
		return isset($this->groups[$group]) ? $this->groups[$group] : $this->groups["default"];
	}
	
	/** 
	 * @param $unban - разбан в минутах
	 */
	public function ban(string $player, string $banby, string $reason, int $unban) : void
	{
		$this->banlist->set(strtolower($player), ["banby" => $banby, "reason" => $reason, "unban" => time() + 60 * $unban]);
		$this->banlist->save();
		
		if($this->getServer()->getPlayerExact($player) !== null)
		{
			$this->getServer()->getPlayerExact($player)->kick(str_replace(["{banby}", "{reason}", "{unban}"], [$banby, $reason, $unban], "Вас забанил: §e{banby}\n§rПричина: §c{reason}\n§rРазбан через: §a{unban} §rминут"), false);
		}
	}
	
	public function unban(string $player) : void
	{
		$this->banlist->remove(strtolower($player));
		$this->banlist->save();
	}
	
	/**
	 * @return array|null
	 */
	public function getData(string $player)
	{
		return $this->banlist->exists(strtolower($player)) ? $this->banlist->get(strtolower($player)) : null;
	}
}