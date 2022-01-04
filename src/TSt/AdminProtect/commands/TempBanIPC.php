<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\Loader;
use TSt\AdminProtect\APIs\APCommand;
use DateTime;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class TempBanIPC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "tempban-ip", "Ban specified player", "/tempban-ip <player> <date or duration> [reason...]", null, ["tban-ip", "tbanip", "tempbanip"]);
        $this->setPermission("adminprotect.ban.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /tempbanip <{$this->cfg->get("Player")}|{$this->cfg->get("IP")}> <{$this->cfg->get("Date")}> [{$this->cfg->get("Reason")}...]");
        }else{
            
            $name = array_shift($args);
            $until = array_shift($args);
            $reason = implode(' ', $args);
            if($reason == ''){
                $reason = $this->cfg->get('DefaultBanReason');
            }
            
            if(DateTime::createFromFormat("d.m.Y", $until) !== false){
                $banTime = strtotime($until);
            }else{
                $time = preg_replace("/(\d+)(h)(\d+|$)/i", '${1}hours${3}', $until);
                $time = preg_replace("/(\d+)(m)(\d+|$)/i", '${1}minutes${3}', $time);
                $time = preg_replace("/(\d+)(mo)(\d+|$)/i", '${1}month${3}', $time);
                $time = preg_replace("/(\d+)(s)(\d+|$)/i", '${1}seconds${3}', $time);
                $time = preg_replace("/(\d+)(w)(\d+|$)/i", '${1}weeks${3}', $time);
                $time = preg_replace("/(\d+)(d)(\d+|$)/i", '${1}days${3}', $time);
                $time = preg_replace("/(\d+)(y)(\d+|$)/i", '${1}years${3}', $time);
                $banTime = strtotime(date('d.m.Y H:i:s').' +'.$time);
            }
            if($banTime === false){
                $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("DateFormatError")}");
            }else{
                $dt = \DateTime::createFromFormat("d.m.Y H:i:s", date("d.m.Y H:i:s", $banTime));
                $p = $sender->getServer()->getPlayerExact($name);
                if($sender instanceof Player){
                    $adminName = $sender->getNameTag();
                }else{
                    $adminName = $this->cfg->get("Console");;
                }
                
                $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('TempBannedIPKickMessage'));
                $kick_message = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $kick_message);
                $kick_message = str_replace("%reason%", $reason, $kick_message);
                
                if(($p = $sender->getServer()->getPlayerByPrefix($name)) instanceof Player){
                    if($p->hasPermission("adminprotect.banip.protect" )){
                        if($sender instanceof Player and !$sender->hasPermission("adminprotect.banip.use.protected")){
                            $sender->sendMessage("§4[AdminProtect]§c {$this->cfg->get("CantBanPlayer")}");
                        }else{
                            $sender->getServer()->getIPBans()->addBan($p->getNetworkSession()->getIp(), $reason, $dt, $adminName);
                            // if also block network, banned player will not see server status
                            // and "You are banned" message when trying to connect
                            // I don't know, block adress or not...
                            //$sender->getServer()->getNetwork()->unblockAddress($p->getNetworkSession()->getIp());
                            //$sender->getServer()->getNetwork()->blockAddress($p->getNetworkSession()->getIp(), abs(date("U")-$dt->getTimestamp()));
                            $players = $sender->getServer()->getOnlinePlayers();
                            foreach ($players as $player){
                                if($player->getNetworkSession()->getIp() === $p->getNetworkSession()->getIp()){
                                    $player->kick($kick_message);
                                }
                            }
                            
                            $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanIPBroadcast'));
                            $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                            $broadcast = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $broadcast);
                            $broadcast = str_replace("%reason%", $reason, $broadcast);
                            $sender->getServer()->broadcastMessage($broadcast);
                        }
                    }else{
                        $sender->getServer()->getIPBans()->addBan($p->getNetworkSession()->getIp(), $reason, $dt, $adminName);
                        
                        // if also block network, banned player will not see server status
                        // and "You are banned" message when trying to connect
                        // I don't know, block adress or not...
                        //$sender->getServer()->getNetwork()->unblockAddress($p->getNetworkSession()->getIp());
                        //$sender->getServer()->getNetwork()->blockAddress($p->getNetworkSession()->getIp(), abs(date("U")-$dt->getTimestamp()));
                        $players = $sender->getServer()->getOnlinePlayers();
                        foreach ($players as $player){
                            if($player->getNetworkSession()->getIp() === $p->getNetworkSession()->getIp()){
                                $player->kick($kick_message);
                            }
                        }
                        
                        $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanIPBroadcast'));
                        $broadcast = str_replace("%player%", $p->getNameTag(), $broadcast);
                        $broadcast = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $broadcast);
                        $broadcast = str_replace("%reason%", $reason, $broadcast);
                        $sender->getServer()->broadcastMessage($broadcast);
                    }
                    
                }else{
                    if(!($sender instanceof Player) or $sender->hasPermission("adminprotect.banip.use.offline")){
                        if($this->getPlugin()->isIPValid($name)){
                            $sender->getServer()->getIPBans()->addBan($name, $reason, $dt, $adminName);
                            
                            // if also block network, banned player will not see server status
                            // and "You are banned" message when trying to connect
                            // I don't know, block adress or not...
                            //$sender->getServer()->getNetwork()->unblockAddress($p->getNetworkSession()->getIp());
                            //$sender->getServer()->getNetwork()->blockAddress($name, abs(date("U")-$dt->getTimestamp()));
                            $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('TempBanIPBroadcast'));
                            $broadcast = str_replace("%player%", $name, $broadcast);
                            $broadcast = str_replace("%duration%", date("d.m.Y H:i:s", $banTime), $broadcast);
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
        }
        return true;
    }
}