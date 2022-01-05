<?php
namespace TSt\AdminProtect;

use TSt\AdminProtect\commands\BanC;
use TSt\AdminProtect\commands\BanIPC;
use TSt\AdminProtect\commands\UnbanC;
use TSt\AdminProtect\commands\KickC;
use TSt\AdminProtect\commands\UnbanIPC;
use TSt\AdminProtect\commands\TempBanC;

use DateTime;
use pocketmine\event\Listener;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use TSt\AdminProtect\APIs\EventListener;
use pocketmine\permission\PermissionManager;
use pocketmine\permission\Permission;
use TSt\AdminProtect\commands\TempBanIPC;

class Loader extends PluginBase implements Listener{
    public $banInfoAPI = null;
    public $hasBanInfoPlugin = false;
    public function onLoad():void{
        $this->checkConfig();
		$this->registerCommands();
    }
    public function onEnable():void{
		$banInfoPlugin = $this->getServer()->getPluginManager()->getPlugin("BanInfo");
		if($banInfoPlugin != null){
		    $this->hasBanInfoPlugin = $this->checkCompatibility($banInfoPlugin);
		}
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	//	$event = new EventListener($this);
	//	$this->getServer()->getPluginManager()->registerEvent("PlayerPreLoginEvent", $event, 1, $this);
		$perms = new PermissionManager();
		$perms->addPermission(new Permission("adminprotect.*", "All plugin permissions", ["adminprotect.ban.protect", "adminprotect.ban.use", "adminprotect.ban.use.offline", "adminprotect.ban.use.protected", "adminprotect.tempban.protect", "adminprotect.tempban.use", "adminprotect.tempban.use.offline", "adminprotect.tempban.use.protected", "adminprotect.kick.protect", "adminprotect.kick.use", "adminprotect.kick.use.protected", "adminprotect.unban.use", "adminprotect.unbanip.use", "adminprotect.banip.protect", "adminprotect.banip.use", "adminprotect.banip.use.protected", "adminprotect.banip.use.permanent"]));
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
		if(!$cfg->exists("version") || $cfg->get("version") !== "0.1.0"){
            $this->getLogger()->notice("Detected old or broken config.yml version!");
            $this->getLogger()->notice("Old configuration file saved as config.old.yml");
            $this->getLogger()->notice("Saving default configurtion file...");
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.old.yml");
            $this->saveDefaultConfig();
            $this->reloadConfig();
            $cfg = $this->getConfig();
        }
	}


    private function registerCommands(){
        $defaultCMD = [
            "kick",
            "ban",
            "pardon",
            "ban-ip",
            "pardon-ip"
        ];
        $cmap = $this->getServer()->getCommandMap();
        forEach($defaultCMD as $cmd){
            $command = $cmap->getCommand($cmd);
            $cmap->unregister($command);
        }
        $this->getServer()->getCommandMap()->registerAll("AdminProtect", [
            new BanC($this),
            new KickC($this),
            new UnbanC($this),
            new BanIPC($this),
            new UnbanIPC($this),
            new TempBanC($this),
            new TempBanIPC($this),
		]);
	}
	
	
	/**
	 * Check if input string is correct IPv4 or IPv6
	 * 
	 * @param String $ip
	 * @return bool
	 */
	public function isIPValid($ip) : bool{
	    $ipv4 = "[0-9]{1,3}(\.[0-9]{1,3}){3}";
	    $ipv6 = "[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}";
	    return preg_match("/^($ipv4|$ipv6)\$/", trim($ip));
	}
	
	
	/**
	 * Returns parsed to DateTime ban duration or false if parse error
	 * 
	 * @param String $duration
	 * @return int|false
	 */
	public function parseDuration(String $duration):?int{
	    if(DateTime::createFromFormat("d.m.Y", $duration) !== false){
	        $banTime = strtotime($duration);
	    }else{
	        $time = preg_replace("/(\d+)(h)(\d+|$)/i", '${1}hours${3}', $duration);
	        $time = preg_replace("/(\d+)(m)(\d+|$)/i", '${1}minutes${3}', $time);
	        $time = preg_replace("/(\d+)(mo)(\d+|$)/i", '${1}month${3}', $time);
	        $time = preg_replace("/(\d+)(s)(\d+|$)/i", '${1}seconds${3}', $time);
	        $time = preg_replace("/(\d+)(w)(\d+|$)/i", '${1}weeks${3}', $time);
	        $time = preg_replace("/(\d+)(d)(\d+|$)/i", '${1}days${3}', $time);
	        $time = preg_replace("/(\d+)(y)(\d+|$)/i", '${1}years${3}', $time);
	        $banTime = strtotime(date('d.m.Y H:i:s').' +'.$time);
	    }
	    return $banTime;
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