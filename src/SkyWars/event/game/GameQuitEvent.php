<?php

namespace SkyWars\event\game;

use SkyWars\SkyWars;
use SkyWars\game\Game;
use pocketmine\event\plugin\PluginEvent;
use pocketmine\event\Cancellable;
use pocketmine\Player;

class GameQuitEvent extends PluginEvent implements Cancellable {

	public static $handlerList = null;
	protected $player;
	protected $game;

	public function __construct(SkyWars $plugin, Player $player, Game $game){
		parent::__construct($plugin);
		$this->player = $player;
		$this->game = $game;
	}

	public function getPlayer(){
		return $this->player;
	}

	public function getGame(){
		return $this->game;
	}

}
