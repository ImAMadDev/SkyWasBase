<?php

namespace SkyWars\command;

use SkyWars\SkyWars;
use SkyWars\game\Game;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;

class SkyWarsCommand extends Command{
	protected $base;
	
	public function __construct(SkyWars $base){
		$this->base = $base;
		parent::__construct('sw', 'SkyWarsBase from InAMadDev commands', ' /sw <commands>');
	}
	
	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(empty($args[0])) {
			$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw help');
			return false; 
		}
		switch($args[0]){
			case 'create':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw create <map>');
					return;
				}
				if(!is_dir($this->base->getServer()->getDataPath() . 'worlds/' . $args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cThis world doesn\'t exists.');
					return;
				}
				if($this->base->gameExists($args[1])){
					$sender->sendMessage(SkyWars::PREFIX . '§cThis game already exists.');
					return;
				}
				$this->base->addGameCreator($sender, $args[1]);
			break;
			case 'enable':
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw enable <game>');
					return false;
				}
				if(!($this->base->gameExists($args[1]))){
					$sender->sendMessage(SkyWars::PREFIX . '§cThis game doesn\'t exists.');
					return false;
				}
				if(($game = $this->base->getGameByName($args[1])) instanceof Game){
					if($game->isEnabled()){
						$sender->sendMessage(SkyWars::PREFIX . '§cThis game already is enabled.');
						return false;
					}
					$game->enable();
					$sender->sendMessage(SkyWars::PREFIX . '§aThis game now is enabled.');
					return true;
				}
			break;
			case 'disable':
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return false;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw enable <game>');
					return false;
				}
				if(!($this->base->gameExists($args[1]))){
					$sender->sendMessage(SkyWars::PREFIX . '§cThis game doesn\'t exists.');
					return false;
				}
				if(($game = $this->base->getGameByName($args[1])) instanceof Game){
					if($game->isDisabled()){
						$sender->sendMessage(SkyWars::PREFIX . '§cThis game already is disabled.');
						return false;
					}
					$game->disable();
					$sender->sendMessage(SkyWars::PREFIX . '§cThis game now is disabled.');
					return true;
				}
			break;
			case 'setlobby':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return false;
				}
				$this->base->getPlayerCreator($sender)->setLobby();
			break;
			case 'setspect':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return;
				}
				$this->base->getPlayerCreator($sender)->setSpect();
			break;
			case 'addspawn':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return;
				}
				$this->base->getPlayerCreator($sender)->addSpawn();
			break;
			case 'setslots':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw setslots <game> <slots>');
					return false;
				}
				if(!is_numeric($args[1])){
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw setslots <in numeric args 2, 3, 4...>');
					return false;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return false;
				}
				$this->base->getPlayerCreator($sender)->setSlots($args[1]);
			break;
			case 'setteams':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw setteams <teams>');
					return false;
				}
				if(!is_numeric($args[1])){
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw setteams <in numeric args 2, 3, 4...>');
					return false;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return false;
				}
				$this->base->getPlayerCreator($sender)->setTeams($args[1]);
			break;
			case 'setplayersperteam':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw setplayersperteam <Playersperteam>');
					return false;
				}
				if(!is_numeric($args[1])){
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw setplayersperteam <in numeric args 2, 3, 4...>');
					return false;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return false;
				}
				$this->base->getPlayerCreator($sender)->setPlayersPerTeam($args[1]);
			break;
			case 'setminy':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return false;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return false;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw setminy <game>');
					return false;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return false;
				}
				$this->base->getPlayerCreator($sender)->setMinY();
			break;
			case 'setmaxy':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return false;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return false;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw setmaxy <game>');
					return false;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return false;
				}
				$this->base->getPlayerCreator($sender)->setMaxY();
			break;
			case 'save':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return false;
				}
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return false;
				}
				if(empty($args[1])) {
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw save <game>');
					return false;
				}
				if(!($this->base->creatorExists($sender))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou aren\'t in creator mode.');
					return false;
				}
				$this->base->getPlayerCreator($sender)->saveData();
			break;
			case 'join':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return false;
				}
				if(empty($args[1])) {
					$this->base->getRandomGame();
					$sender->sendMessage(SkyWars::PREFIX . '§cUsage /sw join <game>');
					return false;
				}
				if(!($this->base->gameExists($args[1]))){
					$sender->sendMessage(SkyWars::PREFIX . '§cThis game doesn\'t exists.');
					return false;
				}
				if(($game = $this->base->getGameByName($args[1])) instanceof Game){
					$sender->sendMessage(SkyWars::PREFIX . '§aSending you to a game .');
					$game->joinGame($sender);
					return true;
				}
			break;
			case 'leave':
				if(!($sender instanceof Player)){
					$sender->sendMessage(SkyWars::PREFIX . '§cRun this command in game');
					return false;
				}
				if(($game = $this->base->getGameByName($sender->getLevel()->getFolderName())) instanceof Game){
					$sender->sendMessage(SkyWars::PREFIX . '§cWe\'re sending you to the lobby.');
					$game->quitGame($sender, true);
					return true;
				}
			break;
			case 'help':
				if(!($sender->hasPermission('skywars.admin'))){
					$sender->sendMessage(SkyWars::PREFIX . '§cYou doesn\'t have permissions for use this command');
					return false;
				}
				$sender->sendMessage(SkyWars::PREFIX . '§ecommands!');
				$sender->sendMessage('§a/sw help');
				$sender->sendMessage('§a/sw create <game>');
				$sender->sendMessage('§a/sw setslots <game> <slots>');
				$sender->sendMessage('§a/sw setlobby <game>');
				$sender->sendMessage('§a/sw setminy <game>');
				$sender->sendMessage('§a/sw setmaxy <game>');
				$sender->sendMessage('§a/sw addspawn <game>');
				$sender->sendMessage('§a/sw enable <game>');
				$sender->sendMessage('§a/sw enable <game>');
			break;
			default:
				$sender->sendMessage(SkyWars::PREFIX . '§cUsage: /sw help.');
			break;
		}
	}
}