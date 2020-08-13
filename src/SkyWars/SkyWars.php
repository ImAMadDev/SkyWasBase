<?php

namespace SkyWars;

use pocketmine\plugin\PluginBase;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\entity\Entity;
use SkyWars\database\DataBase;
use SkyWars\utils\ZipperUtils;
use SkyWars\database\player\PlayerData;
use SkyWars\game\Game;
use SkyWars\game\utils\GameAPI;
use SkyWars\game\utils\GameCreator;
use SkyWars\game\task\SignTick;
use SkyWars\command\SkyWarsCommand;
use SkyWars\SkyWarsListener;
use SkyWars\kit\KitManager;
use SkyWars\libs\scoreboard\ScoreAPI;
use SkyWars\libs\combatlogger\CombatManager;
use SkyWars\libs\fireworks\entity\FireworksRocket;
use function count;
use function in_array;
use function is_dir;
use function is_file;

class SkyWars extends PluginBase{
	
	private $games = [];
	private $playerManager;
	private static $instance;
	public $zip;
	public $gameCreator;
	private $arenaConfig;
	private $combatManager;
	public $pluginConfig;
	private $kitManager;
	
	const PREFIX = "§l§6SkyWars §r";
	
	public function onLoad(): void{
		$this->loadConfiguration();
		self::$instance = $this;
		$this->zip = new ZipperUtils($this);
		$this->playerManager = new PlayerData($this);
		$this->kitManager = new KitManager($this);
		$this->combatManager = new CombatManager($this);
		$this->getLogger()->notice(self::PREFIX . 'plugin instance loaded');
	}
	public function onEnable(): void{
		$this->arenaConfig = new DataBase($this->getDataFolder() . 'gamesData.js', DataBase::JSON);
		$this->registerFolders();
		$this->getServer()->getPluginManager()->registerEvents(new SkyWarsListener($this), $this);
		$this->getServer()->getPluginManager()->registerEvents(new ScoreAPI(), $this);
		$this->getServer()->getCommandMap()->register("sw", new SkyWarsCommand($this));
		$this->intGames();
		Entity::registerEntity(FireworksRocket::class);
		if($this->getJoinMode() == 'sign'){
			$this->getScheduler()->scheduleRepeatingTask(new SignTick($this), 5);
		}
	}
	
	protected function loadConfiguration(): void{
		$this->pluginConfig = new DataBase($this->getDataFolder() . 'Configurations.js', DataBase::JSON);
		if(!($this->pluginConfig->get('JoinMode'))){
			$this->pluginConfig->set('JoinMode', 'sign');
		}
		$this->pluginConfig->save();
	}
	
	public function getJoinMode(): string{
		return $this->pluginConfig->get('JoinMode');
	}
	
	public function getCombatManager(): CombatManager{
		return $this->combatManager;
	}
	
	public function getPlayerManager(): PlayerData{
		return $this->playerManager;
	}
	
	public function getKitsManager(): KitManager{
		return $this->kitManager;
	}
	
	public static function getInstance(): SkyWars{
		return self::$instance;
	}
	
	public function addGameCreator(Player $player, string $game): void{
		$this->gameCreator[$player->getRawUniqueId()] = new GameCreator($player, $game, $this);
	}
	
	public function removeGameCreator(Player $player): void{
		if($this->creatorExists($player)){
			unset($this->gameCreator[$player->getRawUniqueId()]);
			$player->sendMessage(SkyWars::PREFIX . '§cSession has been destroyed.');
		}
	}
	
	public function creatorExists(Player $player): bool{
		return in_array($player->getRawUniqueId(), array_keys($this->gameCreator));
	}
	
	public function getPlayerCreator(Player $player): ?GameCreator{
		if(!isset($this->gameCreator[$player->getRawUniqueId()])){
			return null;
		} else {
			return $this->gameCreator[$player->getRawUniqueId()];
		}
	}
	
	public function registerFolders(){
		if(!is_dir($this->getDataFolder() . 'Games/')){
			@mkdir($this->getDataFolder() . 'Games/');
		}
		if(!is_dir($this->getDataFolder() . 'PlayerData/')){
			@mkdir($this->getDataFolder() . 'PlayerData/');
		}
		if($this->arenaConfig->get('Games')){
			foreach($this->arenaConfig->get('Games') as $game){
				if(!is_dir($this->getDataFolder() . 'Games/' . $game . '/')){
					@mkdir($this->getDataFolder() . 'Games/' . $game . '/');
				}
			}
		}
	}
	
	public function gameExists(string $game): bool{
		return in_array($game, array_keys($this->games));
	}
	
	public function getGameByName(string $name):? Game{
		if(!isset($this->games[$name])){
			return null;
		} else {
			return $this->games[$name];
		}
	}
	
	public function addGame(string $arena, int $slots, float $minY, float $maxY, array $spawns, $lobby, $spect){
		if(!is_dir($this->getDataFolder() . 'Games/' . $arena . '/')){
			@mkdir($this->getDataFolder() . 'Games/' . $arena . '/');
		}
		$configData = new DataBase($this->getDataFolder() . 'Games/' . $arena . '/data.js', DataBase::JSON);
		$arenaData = array(
			'arenaName' => $arena,
			'status' => GameAPI::GAME_STATUS_DISABLE,
			'slots' => $slots,
			'minY' => $minY,
			'maxY' => $maxY,
			'spawns' => $spawns,
			'lobby' => $lobby,
			'spect' => $spect,
			'playersPerTeam' => $playersPerTeam,
			'teams' => $teams
		);
		foreach($arenaData as $data => $value){
			$configData->set($data, $value);
		}
		$configData->save();
		$this->games[$arena] = new Game($this, $arenaData);
		$this->arenaConfig->set('Games', array_keys($this->games));
		$this->arenaConfig->save();
		if(!is_file($this->getDataFolder() . 'Games/' . $arena . '/' . $arena . '.zip')){
			if($this->getServer()->getLevelByName($arena) instanceof Level){
				$this->zip->mkZip($this->getServer()->getLevelByName($arena));
			}
		}
		$this->zip->unZip($arena);
		$this->getLogger()->info(self::PREFIX . ' arena '. $arena. ' successful created');
	}
	
	public function loadGame(string $arena): bool
	{
		if (is_file($this->getDataFolder() . 'Games/' . $arena . '/data.js')) {
			$configData = new DataBase($this->getDataFolder() . 'Games/' . $arena . '/data.js', DataBase::JSON);
			$arenaData = array(
			'arenaName' => $arena,
			'status' => $configData->get("status"),
			'slots' => $configData->get("slots"),
			'minY' => $configData->get("minY"),
			'maxY' => $configData->get("maxY"),
			'spawns' => $configData->get('spawns'),
			'lobby' => $configData->get('lobby'),
			'spect' => $configData->get('spect'),
			'playersPerTeam' => $configData->get('playersPerTeam', 1),
			'teams' => $configData->get('teams', 0)
		);
		$this->games[$arena] = new Game($this, $arenaData);
		if(!is_file($this->getDataFolder() . 'Games/' . $arena . '/' . $arena . '.zip')){
			if($this->getServer()->getLevelByName($arena) instanceof Level){
				$this->zip->mkZip($this->getServer()->getLevelByName($arena));
			}
		}
		$this->zip->unZip($arena);
		$this->getLogger()->info(self::PREFIX . ' arena '. $arena. ' successful created');
		return true;
		} else {
			$this->getLogger()->info(self::PREFIX . ' arena data from '. $arena. ' isnt exist');
			return false;
		}
	}
	
	public function getRandomGame(): ? Game{
		if(count($this->games) > 0){
			$games = array();
			foreach($this->games as $game){
				if(!($game->isDisabled())){
					if($game->canJoin()){
						$games[] = $game;
					}
				}
			}
			return $games[rand(0, count($games) -1)];
		}
		return null;
	}
	
	public function intGames(){
		if($this->arenaConfig->get('Games')){
			foreach($this->arenaConfig->get('Games') as $game){
				$this->loadGame($game);
			}
		}
	}
}
?>