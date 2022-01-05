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
            $reason = ($r === '') ? $this->cfg->get('DefaultKickReason') : $r;
            
            if($sender instanceof Player){
                $adminName = $sender->getNameTag();
            }else{
                $adminName = $this->cfg->get("Console");
            }
            $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('BannedIPKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
        
            if(($p = $sender->getServer()->getPlayerByPrefix($name)) instanceof Player){
                if($p->hasPermission("adminprotect.banip.protect" )){
                    if($sender instanceof Player and !$sender->hasPermission("adminprotect.banip.use.protected")){
                        $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                    }else{
                        $sender->getServer()->getIPBans()->addBan($p->getNetworkSession()->getIp(), $reason, null, $adminName);
                        
                        // if also block network, banned player will not see server status
                        // and "You are banned" message when trying to connect
                        // I don't know, block adress or not...
                        //$sender->getServer()->getNetwork()->blockAddress($p->getNetworkSession()->getIp(), -1);
                        $players = $sender->getServer()->getOnlinePlayers();
                        foreach ($players as $player){
                            if($player->getNetworkSession()->getIp() === $p->getNetworkSession()->getIp()){
                                $player->kick($kick_message);
                            }
                        }
                        
                        $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanIPBroadcast'));
                        $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                        $broadcast = str_replace("%reason%", $reason, $broadcast);
                        $sender->getServer()->broadcastMessage($broadcast);
                    }
                }else{
                    $sender->getServer()->getIPBans()->addBan($p->getNetworkSession()->getIp(), $reason, null, $adminName);
                    
                    // if also block network, banned player will not see server status
                    // and "You are banned" message when trying to connect
                    // I don't know, block adress or not...
                    //$sender->getServer()->getNetwork()->blockAddress($p->getNetworkSession()->getIp(), -1);
                    $players = $sender->getServer()->getOnlinePlayers();
                    foreach ($players as $player){
                        if($player->getNetworkSession()->getIp() === $p->getNetworkSession()->getIp()){
                            $player->kick($kick_message);
                        }
                    }
                    
                    $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanIPBroadcast'));
                    $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                    $broadcast = str_replace("%reason%", $reason, $broadcast);
                    $sender->getServer()->broadcastMessage($broadcast);
                }
            
            }else{
                if(!($sender instanceof Player) or $sender->hasPermission("adminprotect.banip.use.offline")){
                    if($this->getPlugin()->isIPValid($name)){
                        $sender->getServer()->getIPBans()->addBan($name, $reason, null, $adminName);
                        
                        // if also block network, banned player will not see server status
                        // and "You are banned" message when trying to connect
                        // I don't know, block adress or not...
                        //$sender->getServer()->getNetwork()->blockAddress($name, -1);
                        $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('BanIPBroadcast'));
                        $broadcast = str_replace("%player%", $name, $broadcast);
                        $broadcast = str_replace("%reason%", $reason, $broadcast);
                        $sender->getServer()->broadcastMessage($broadcast);
                        $players = $sender->getServer()->getOnlinePlayers();
                        foreach ($players as $player){
                            if($player->getNetworkSession()->getIp() === $name){
                                $player->kick($kick_message);
                            }
                        }
                    }else{
                        $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("IncorrectIP")} {$this->cfg->get("forBan")}");
                    }
                }else{
                    $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("CantBanPOffline")}");
                }
                
            }
            
        }
        return true;
    }
}