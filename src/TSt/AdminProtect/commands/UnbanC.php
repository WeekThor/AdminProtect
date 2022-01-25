<?php
namespace TSt\AdminProtect\commands;

use TSt\AdminProtect\APIs\APCommand;
use TSt\AdminProtect\Loader;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
class UnbanC extends APCommand{
    private $cfg;
    public function __construct(Loader $plugin){
        parent::__construct($plugin, "pardon", "Unban nickname", "/pardon <player>", null, ["unban"]);
        $this->setPermission("adminprotect.unban.use");
    }
    public function execute(CommandSender $sender, $alias, array $args): bool{
        if(!$this->testPermission($sender)){
            return false;
        }
        $this->cfg = $this->getPlugin()->getConfig();
        if(count($args) === 0){
            $sender->sendMessage("§4[AdminProtect]§c /unban <{$this->cfg->get("Player")}>");
        }else{
            
            if($sender instanceof Player){
                $admin = $sender->getNameTag();
            }else{
                $admin = $this->cfg->get("Console");
            }
            $name = array_shift($args);
            $bannedPlayer = $this->getPlugin()->getServer()->getNameBans()->getEntry($name);
            if($bannedPlayer !== null){
                if($sender instanceof Player){
                    if($sender->hasPermission("adminprotect.unban.except.".mb_strtolower($bannedPlayer->getSource()))){
                        $sender->sendMessage("§4[AdminProtect] §c".str_replace("%sender%", $bannedPlayer->getSource(), $this->cfg->get("CantUnbanBannedBy")));
                        return false;
                    }
                }
                $broadcast = $this->cfg->get("UnbanBroadcast");
                $broadcast = str_replace("%sender%", $admin, $broadcast);
                $broadcast = str_replace("%player%", $name, $broadcast);
                $sender->getServer()->getNameBans()->remove($name);
                $sender->getServer()->broadcastMessage($broadcast);
            }else{
                $sender->sendMessage("§4[AdminProtect] §c{$this->cfg->get("PlayerNotBanned")}");
            }
        }
        return true;
    }
}