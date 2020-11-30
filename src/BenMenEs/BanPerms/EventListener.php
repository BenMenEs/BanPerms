<?php

declare(strict_types=1);

namespace BenMenEs\BanPerms;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerPreLoginEvent;

class EventListener implements Listener
{
	
	/** @var API */
	private $api;
	
	public function __construct(API $api)
	{
		$this->api = $api;
	}
	
	public function onLogin(PlayerPreLoginEvent $event) : void
	{
		$player = $event->getPlayer();
		$nick = $player->getName();
		$data = $this->api->getData($nick);
		
		if($data !== null)
		{
			if($data["unban"] > time())
			{
				$player->kick(str_replace(["{banby}", "{reason}", "{unban}"], [$data["banby"], $data["reason"], date("Y-m-d H:i:s", $data["unban"])], "Вас забанил: §e{banby}\n§rПричина: §c{reason}\n§rРазбан: §a{unban}"), false);
			} else
			{
				$this->main->unban($nick);
			}
		}
	}
}