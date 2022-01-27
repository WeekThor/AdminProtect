<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\APCommand;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
class BanIPC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "ban-ip", "Ban specified IP", "/ban-ip <player|adress> [reason...]", null, ["banip"]);
        $this->setPermission("adminprotect.banip.use.permanent");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /banip <{$this->cfg->get("Player")}|{$this->cfg->get("IP")}> [{$this->cfg->get("Reason")}...]");
        }else{
            $name = array_shift($args);
            $r = trim(implode(" ", $args));
            $reason = ($r === '') ? $this->cfg->get('DefaultBanReason') : $r;
            
            if($sender instanceof Player){
                $adminName = $sender->getNameTag();
                $admin = $sender->getName();
            }else{
                $adminName = $this->cfg->get("Console");
                $admin = $adminName;
            }
            $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('BannedIPKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
            $ip = "";
            $broadcast = "";
        
            $bannedPlayer = $this->getPlugin()->getServer()->getIPBans()->getEntry($name);
            if($bannedPlayer !== null){
                if($sender instanceof Player and $sender->hasPermission("adminprotect.unban.except.".mb_strtolower($bannedPlayer->getSource()))){
                    $sender->sendMessage("§4[AdminProtect] §c".str_replace("%sender%", $bannedPlayer->getSource(), $this->cfg->get("CantEditBan")));
                    return false;
                }
            }
            if(($p = $sender->getServer()->getPlayerByPrefix($name)) instanceof Player){
                $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanIPBroadcast'));
                $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                $broadcast = str_replace("%reason%", $reason, $broadcast);
                if($p->hasPermission("adminprotect.banip.protect" )){
                    if($sender instanceof Player and !$sender->hasPermission("adminprotect.banip.use.protected")){
                        $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                        return false;
                    }
                }
                $ip = $p->getNetworkSession()->getIp();
            }else{
                $ip = $name;
                $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanIPBroadcast'));
                $broadcast = str_replace("%player%", $ip, $broadcast);
                $broadcast = str_replace("%reason%", $reason, $broadcast);
                if($sender instanceof Player and !$sender->hasPermission("adminprotect.banip.use.offline")){
                    $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("CantBanOffline")}");
                    return false;
                }
                if(!$this->getPlugin()->isIPValid($ip)){
                    $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("IncorrectIP")} {$this->cfg->get("forBan")}");
                    return false;
                }
            }
            $sender->getServer()->getIPBans()->addBan($ip, $reason, null, $admin);
                    
            // if also block network, banned player will not see server status
            // and "You are banned" message when trying to connect
            // I don't know, block adress or not...
            //$sender->getServer()->getNetwork()->blockAddress($name, -1);
            
            $sender->getServer()->broadcastMessage($broadcast);
            $players = $sender->getServer()->getOnlinePlayers();
            foreach ($players as $player){
                if($player->getNetworkSession()->getIp() === $ip){
                    $player->kick($kick_message);
                 }
            }
            
        }
        return true;
    }
}