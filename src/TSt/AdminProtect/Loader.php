<?php
namespace TSt\AdminProtect;

use TSt\AdminProtect\commands\BanC;
use TSt\AdminProtect\commands\BanIPC;
use TSt\AdminProtect\commands\UnbanC;
use TSt\AdminProtect\commands\KickC;
use TSt\AdminProtect\commands\UnbanIPC;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

class Loader extends PluginBase{
    public $banInfoAPI = null;
    public function onLoad(){
        $this->checkConfig();
		$this->registerCommands();
    }
	public function onEnable(){
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
            $this->getLogger()->notice("Detected old or broken config.yml version!");
            $this->getLogger()->notice("Old configuration file saved as config.old.yml");
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
                //$this->getLogger()->notice("Error while deleting command: command /{$commandlabel} not found.");
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
	    $version = explode(' ', $plugin->getDescription()->getVersion());
	    $version = explode('-', $version[0]);
	    $version = explode('.', $version[0]);
	    $min_version = [1,14,1];
	    $api_version = '1.0.0';
	    if((intval($version[0]) == $min_version[0] AND ((intval($version[1]) == $min_version[1] AND intval($version[2]) >= $min_version[2]) OR intval($version[1]) > $min_version[1])) OR intval($version[0]) >= $min_version[0]){
	        if($plugin->getDescription()->getAuthors()[0] == 'WeekThor'){
	            $this->getServer()->getLogger()->notice("[AdminProtect] Plugin BanInfo v".implode('.', $version)." detected!");
	            try{
	                $this->banInfoAPI = $plugin->getAPI($api_version);
	                if($this->banInfoAPI != null){
	                    $this->getServer()->getLogger()->notice("[AdminProtect] Enabled additional functionality.");
	                    return true;
	                }else{
	                    $this->getServer()->getLogger()->notice("[AdminProtect] BanInfo connection failed. Is AdminProtect outdated?");
	                    return false;
	                }
	            }catch (\Exception $e){
	                $this->getServer()->getLogger()->notice("[AdminProtect] BanInfo connection failed. Do you have an official BanInfo version?");
	                return false;
	            }
	        }else{
	            return false;
	        }
	    }else{
	        $this->getServer()->getLogger()->notice("[AdminProtect] An incompatible plugin BanInfo version was found: ".implode('.', $version)."!");
	        $this->getServer()->getLogger()->notice("[AdminProtect] Required version: ". implode('.', $min_version) . " or higher");
	        $this->getServer()->getLogger()->notice("[AdminProtect] Additional functionality was not enabled.");
	        return false;
	    }
	    
	}
}