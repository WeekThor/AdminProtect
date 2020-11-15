<?php
namespace AdminProtect;

use AdminProtect\commands\BanC;
use AdminProtect\commands\BanIPC;
use AdminProtect\commands\UnbanC;
use AdminProtect\commands\KickC;
use AdminProtect\commands\UnbanIPC;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Loader extends PluginBase{
    public $hasBanInfoPlugin = false;
    public function onLoad(){
        $this->getServer()->getLogger()->info("§6[AdminProtect]§e Loading....");
        $this->checkConfig();
		$this->registerCommands();
    }
	public function onEnable(){
		$this->getServer()->getLogger()->info("§2[AdminProtect]§a Enabled!");
		$banInfoPlugin = $this->getServer()->getPluginManager()->getPlugin("BanInfo");
		if($banInfoPlugin != null){
		    $this->hasBanInfoPlugin = $this->checkCompatibility($banInfoPlugin);
		}
	}
	public function checkConfig(){
        if(!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
        }
        if(!file_exists($this->getDataFolder() . "config.yml")){
            $this->saveDefaultConfig();
        }
        $this->saveResource("config.yml");
		$cfg = $this->getConfig();
		if(!$cfg->exists("version") || $cfg->get("version") !== "0.0.8"){
            $this->getLogger()->error("Detected old or broken config.yml version!");
            $this->getLogger()->error("Old configuration file saved as config.old.yml");
            $this->getLogger()->notice("Saving default configurtion file...");
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.old.yml");
            $this->saveDefaultConfig();
            $this->reloadConfig();
            $cfg = $this->getConfig();
        }
	}


	private function unregisterCommands(array $commands){
        $commandmap = $this->getServer()->getCommandMap();
        foreach($commands as $commandlabel){
            $command = $commandmap->getCommand($commandlabel);
            try{
                $command->setLabel($commandlabel . "_disabled");
                $command->unregister($commandmap);
            }catch (\Error $e){
                $this->getLogger()->notice("Error while deleting command: command /{$commandlabel} not found.");
            }
        }
    }
    private function registerCommands(){
        $this->unregisterCommands([
            "kick",
            "ban",
            "ban-ip",
            "pardon",
            "unban",
            "unban-ip",
            "pardon-ip"
        ]);
        $this->getServer()->getCommandMap()->registerAll("AdminProtect", [
            new BanC($this),
            new KickC($this),
            new UnbanC($this),
            new BanIPC($this),
            new UnbanIPC($this)
		]);
	}
    

	public function Config($config = "config.yml") : Config{
		return $this->getConfig($config);
	}
	
	public function isIPValid($ip) : bool{
	    $ipv4 = "[0-9]{1,3}(\.[0-9]{1,3}){3}";
	    $ipv6 = "[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}";
	    return preg_match("/^($ipv4|$ipv6)\$/", trim($ip));
	}
    
	private function checkCompatibility(Plugin $plugin) : bool{
	    $version = explode('.', $plugin->getDescription()->getVersion());
	    $min_version = [1,13,0];
	    $return = false;
	    if(intval($version[0]) >= $min_version[0]){
	        if(intval($version[1]) >= $min_version[1]){
	            if(intval($version[2]) >= $min_version[2]){
	                $return = true;
	            }
	        }
	    }
	    if($return){
	        $this->getServer()->getLogger()->info("§2[AdminProtect]§a Plugin BanInfo v".implode('.', $version)." detected!");
	        $this->getServer()->getLogger()->info("§2[AdminProtect]§a Enebled additional functionality: check if player banned before unban");
	    }else{
	        $this->getServer()->getLogger()->info("§6[AdminProtect]§e An incompatible plugin BanInfo version was found: ".implode('.', $version)."!");
	        $this->getServer()->getLogger()->info("§6[AdminProtect]§e Required version: ". implode('.', $min_version) . " or higher");
	        $this->getServer()->getLogger()->info("§2[AdminProtect]§a Additional functionality was not enabled.");
	    }
	    return $return;
	}
}