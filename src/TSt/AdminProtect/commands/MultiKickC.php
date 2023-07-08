<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\APCommand;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
class MultiKickC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "multikick", "Kick specified players", "/multikick <players> [reason...]", null, ["mkick"]);
        $this->setPermission("adminprotect.kick.use.multiple");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /mkick <{$this->cfg->get("Player")}> [{$this->cfg->get("Reason")}...]");
        }else{
            $names = explode(',', array_shift($args));
            $r = trim(implode(" ", $args));
            $reason = ($r === '') ? $this->cfg->get('DefaultBanReason') : $r;
            
            if($sender instanceof Player){
                $adminName = $sender->getNameTag();
                $superadmin = $sender->hasPermission("adminprotect.kick.use.protected");
            }else{
                $adminName = $this->cfg->get("Console");
                $superadmin = true;
            }
            $kick_message = str_replace("%sender%", $adminName, $this->cfg->get('KickedPlayerKickMessage'));
            $kick_message = str_replace("%reason%", $reason, $kick_message);
            
            
            foreach($names as $name){
                $p = $sender->getServer()->getPlayerExact($name);
                if($p instanceof Player){
                    if($p->hasPermission("adminprotect.kick.protect" )){
                        if($sender instanceof Player and !$superadmin){
                            $sender->sendMessage("§4[AdminProtect] §c".str_replace(["%error%", "%player%"], [$this->cfg->get("CantKickPlayer"),$name], $this->cfg->get("MultipleKickError")));
                            continue;
                        }
                    }
                    $p->kick($kick_message);
                }else{
                    $sender->sendMessage("§4[AdminProtect] §c".str_replace(["%error%", "%player%"], [$this->cfg->get("PlayerNotFound"),$name], $this->cfg->get("MultipleKickError")));
                    continue;
                }
                $broadcast = str_replace("%sender%", $adminName, $this->cfg->get('KickBroadcast'));
                $broadcast = str_replace("%player%", $name, $broadcast);
                $broadcast = str_replace("%reason%", $reason, $broadcast);
                $sender->getServer()->broadcastMessage($broadcast);
            }
            
        }
        return true;
    }
}