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
use pocketmine\plugin\PluginBase;
use TSt\AdminProtect\APIs\EventListener;
use pocketmine\permission\PermissionManager;
use pocketmine\permission\Permission;
use TSt\AdminProtect\commands\TempBanIPC;

class Loader extends PluginBase implements Listener{
    public function onLoad():void{
        $this->checkConfig();
		$this->registerCommands();
    }
    public function onEnable():void{
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$perms = new PermissionManager();
		$perms->addPermission(new Permission("adminprotect.ban", "Parent of adminprotect.ban.*", ["adminprotect.ban.protect", "adminprotect.ban.use", "adminprotect.ban.use.offline", "adminprotect.ban.use.protected"]));
		$perms->addPermission(new Permission("adminprotect.tempban", "Parent of adminprotect.tempban.*", ["adminprotect.tempban.protect", "adminprotect.tempban.use", "adminprotect.tempban.use.offline", "adminprotect.tempban.use.protected"]));
		$perms->addPermission(new Permission("adminprotect.kick", "Parent of adminprotect.kick.*", ["adminprotect.kick.protect", "adminprotect.kick.use", "adminprotect.kick.use.protected"]));
		$perms->addPermission(new Permission("adminprotect.banip", "Parent of adminprotect.banip.*", ["adminprotect.banip.protect", "adminprotect.banip.use", "adminprotect.banip.use.offline", "adminprotect.banip.use.protected", "adminprotect.banip.use.permanent"]));
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
        $config_version = "0.1.3";
		$cfg = $this->getConfig();
		if(!$cfg->exists("version") || $cfg->get("version") !== $config_version){
            $this->getLogger()->notice("Different version of config found!");
            $this->getLogger()->notice("Old configuration file saved as config.old.yml");
            rename($this->getDataFolder() . "config.yml", $this->getDataFolder() . "config.old.yml");
            $this->getLogger()->notice("Saving default configurtion file...");
            $this->saveDefaultConfig();
            $this->reloadConfig();
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
	 * Returns parsed to unix timestampt ban duration or false if parse error
	 * 
	 * @param String $duration
	 * @return int|bool
	 */
	public function parseDuration(String $duration){
	    if(DateTime::createFromFormat("d.m.Y", $duration) !== false){
	        $banTime = strtotime($duration);
	    }elseif(is_numeric($duration)){
	        $banTime = strtotime(date('d.m.Y H:i:s')." +{$duration}days");
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
}